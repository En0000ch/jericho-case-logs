<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type StatsDashboardData from \AnalyticsWP\Lib\APIServer
 * @psalm-import-type TopSource from \AnalyticsWP\Lib\APIServer
 * @psalm-import-type TopPage from \AnalyticsWP\Lib\APIServer
 * @psalm-import-type TopDevice from \AnalyticsWP\Lib\APIServer
 * @psalm-import-type TopStat from \AnalyticsWP\Lib\APIServer
 * @psalm-type ExtraTopStatFields = array{change_color: string, change_symbol: string, change_formatted: int|float}
 *
 * @psalm-import-type ClientSite from \AnalyticsWP\Lib\AgencyMode
 * 
 * The purpose of this class is to provide a context for the email report,
 * which our templating system can use to render the email report.
 */
class EmailReportContext
{
    /**
     * @var int $NUMBER_OF_TOP
     */
    const NUMBER_OF_TOP = 5;

    /**
     * @var string $DEFAULT_ACCENT_COLOR
     */
    const DEFAULT_ACCENT_COLOR = '#2c5282';

    /**
     * @var StatsDashboardData $stats_dashboard_data
     */
    public $stats_dashboard_data;

    /**
     * @var 'weekly'|'monthly' $report_type
     */
    public $report_type;

    /**
     * @var ClientSite $client_site
     */
    public $client_site;

    /**
     * @param StatsDashboardData $stats_dashboard_data
     * @param 'weekly'|'monthly' $report_type
     * @param ClientSite $client_site
     */
    function __construct($stats_dashboard_data, $report_type, $client_site)
    {
        $this->stats_dashboard_data = $stats_dashboard_data;
        $this->report_type = $report_type;
        $this->client_site = $client_site;
    }

    /**
     * @return string
     */
    public function report_type_capitalized()
    {
        // capitalize the first letter of the report type
        return ucfirst($this->report_type);
    }

    /**
     * @return string
     */
    public function human_readable_date_range()
    {
        $date_range_input = $this->report_type == 'weekly' ? 'Last 7 days' : 'Last 30 days';
        $date_range = new DateRange($date_range_input);

        $date_format_mode = Validators::one_of(['us', 'eu'], 'us', SuperSimpleWP::get_setting('analyticswp', 'agency_mode_date_formatting'));

        return $date_range->human_readable_range($date_format_mode);
    }


    /**
     * @return TopSource[]
     */
    public function top_sources()
    {
        return array_slice($this->stats_dashboard_data['top_sources'], 0, self::NUMBER_OF_TOP);
    }

    /**
     * @return TopPage[]
     */
    public function top_pages()
    {
        return array_slice($this->stats_dashboard_data['top_pages'], 0, self::NUMBER_OF_TOP);
    }

    /**
     * @return TopDevice[]
     */
    public function top_devices()
    {
        return array_slice($this->stats_dashboard_data['top_devices'], 0, self::NUMBER_OF_TOP);
    }

    /**
     * @param TopStat $top_stat
     * @return ExtraTopStatFields&TopStat
     */
    private function add_fields_to_top_stat($top_stat)
    {
        $top_stat['change_color'] = $top_stat['change'] >= 0 ? 'green' : 'red';
        $top_stat['change_symbol'] = $top_stat['change'] >= 0 ? '↑' : '↓';
        $top_stat['change_formatted'] = abs($top_stat['change']);

        // Format the value with commas and remove .0 if present
        $formatted = number_format($top_stat['value'], 1);
        $top_stat['value'] = substr($formatted, -2) === '.0' ? number_format($top_stat['value'], 0) : $formatted;

        return $top_stat;
    }

    /**
     * @return ExtraTopStatFields&TopStat
     */
    public function unique_people_stats()
    {
        return $this->add_fields_to_top_stat($this->stats_dashboard_data['main_graph']['top_stats'][0]);
    }

    /**
     * @return ExtraTopStatFields&TopStat
     */
    public function total_pageviews_stats()
    {
        return $this->add_fields_to_top_stat($this->stats_dashboard_data['main_graph']['top_stats'][1]);
    }

    /**
     * @return ExtraTopStatFields&TopStat
     */
    public function views_per_person_stats()
    {
        return $this->add_fields_to_top_stat($this->stats_dashboard_data['main_graph']['top_stats'][2]);
    }

    /**
     * @return ExtraTopStatFields&TopStat
     */
    public function window_shoppers_rate_stats()
    {
        return $this->add_fields_to_top_stat($this->stats_dashboard_data['main_graph']['top_stats'][3]);
    }

    /**
     * @return string
     */
    public function current_year()
    {
        return date('Y');
    }

    /**
     * @return string
     */
    public function accent_color()
    {
        $var = $this->client_site['assignedColor'];

        if (!empty($var)) {
            return $var;
        } else {
            return self::DEFAULT_ACCENT_COLOR;
        }
    }

    /**
     * @return string
     */
    public function business_logo_url()
    {
        return $this->client_site['logoUrl'];
    }

    /**
     * @return string
     */
    public function business_name()
    {
        return $this->client_site['businessName'];
    }

    /**
     * @return string
     */
    public function business_address()
    {
        return $this->client_site['address'];
    }

    /**
     * @return string
     */
    public function dashboard_url()
    {
        return AgencyMode::get_dashboard_url_for_client_site($this->client_site);
    }

    /**
     * @return string
     */
    public static function agency_name()
    {
        return (string)SuperSimpleWP::get_setting('analyticswp', 'agency_name');
    }

    /**
     * @return string
     */
    public static function agency_contact()
    {
        return (string)SuperSimpleWP::get_setting('analyticswp', 'agency_contact');
    }

    /**
     * @return string
     */
    public static function agency_support_email()
    {
        return (string)SuperSimpleWP::get_setting('analyticswp', 'agency_support_email');
    }
}
