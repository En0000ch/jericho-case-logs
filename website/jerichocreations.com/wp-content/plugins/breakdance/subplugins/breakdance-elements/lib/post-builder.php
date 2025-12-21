<?php

namespace Breakdance\LoopBuilder;

function loopBlockPosts($loop, $filterbar, $propertiesData, $actionData)
{
    $loopIndex = 0;

    while ($loop->have_posts()) {
        $loopIndex++;

        $block = getBlockForLoopIndex($propertiesData, $loopIndex);

        if ($block['type'] !== 'static') {
            $loop->the_post();
        }

        $post = get_post();
        $itemClasses = getItemClasses($post, 'post', $actionData);

        $postTag = $propertiesData['content']['repeated_block']['tag'] ?? 'article';
        $attrs = getFilterAttributesForPost($filterbar, $itemClasses);

        renderBlockPost(
            $actionData,
            $postTag,
            $attrs,
            $block['id']
        );
    }

    renderStaticItemAtEndOfLoopIfPresent($propertiesData, $loopIndex, $filterbar, $actionData);
}

function renderBlockPost($actionData, $postTag, $attrs, $blockId)
{
    $post = get_post();
    bdox_run_action("breakdance_before_loop_item", $post, 'post', $actionData);
    bdox_run_action("breakdance_posts_loop_before_post", $actionData);
?>
    <<?php echo $postTag; ?> <?php echo $attrs; ?>>
        <?php
        if ($blockId) {
            $postId = get_the_ID();
            echo \Breakdance\Render\renderGlobalBlock($blockId, $postId);
        } else {
            if ($_REQUEST['triggeringDocument'] ?? true) {
                echo '<div class="breakdance-empty-ssr-message">Choose a ' . \Breakdance\BreakdanceOxygen\Strings\__bdox('global_block') . ' from the dropdown.</div>';
            } else {
                echo "<!-- Breakdance error: $blockId not found -->";
            }
        }
        ?>
    </<?php echo $postTag; ?>>
<?php
    bdox_run_action("breakdance_posts_loop_after_post", $actionData);
    bdox_run_action("breakdance_after_loop_item", $post, 'post', $actionData);
}

function getWpQuery($propertiesData)
{
    $paged = ($propertiesData['content']['pagination']['pagination'] ?? false) ? \Breakdance\WpQueryControl\getPage() : 0;

    $argsFromQuery = \Breakdance\WpQueryControl\getWpQueryArgumentsFromWpQueryControlProperties(
        $propertiesData['content']['query']['query'] ?? null,
        [
            'paged' => $paged > 1 ? $paged : null,
        ]
    );

    return new \WP_Query($argsFromQuery);
}
