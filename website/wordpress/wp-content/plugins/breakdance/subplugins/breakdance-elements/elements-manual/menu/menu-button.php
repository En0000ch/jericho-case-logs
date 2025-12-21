<?php

namespace EssentialElements;

class MenuButton extends \EssentialElements\Button
{

    static function tag()
    {
        return 'li';
    }

    static function name()
    {
        return 'Menu Button';
    }

    static function additionalClasses()
    {
        return [['name' => 'breakdance-menu-item', 'template' => 'yes'], ['name' => 'breakdance-menu-item--active', 'template' => '{% if is_current_url(content.content.link.url) %}yes{% endif %}']];
    }

    static function slug()
    {
       return __CLASS__;
    }

    static function nestingRule()
    {
        return ["type" => "final", "restrictedToBeADirectChildOf" => ['EssentialElements\MenuBuilder'] ];
    }

    static function category()
    {
        return 'site';
    }

}
