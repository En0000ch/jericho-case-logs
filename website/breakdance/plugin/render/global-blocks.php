<?php

namespace Breakdance\Render;

use Breakdance\Singleton;

class BlockCounterManager
{
    use Singleton;

    /**
     * @var array<string, int>
     */
    private $counter = [];

    /**
     * @var array<int, string>
     */
    private $blocks = [];

    /**
     * Returns the initial block counter value based on POST input.
     * This is necessary when rendering the global block in the builder as each block is fetched via AJAX separately.
     * @return int The initial counter value.
     */
    public function getInitialCounter() {
        /**
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedInferredReturnType
         * @var int
         */
        return filter_input(INPUT_POST, 'currentIndexInMatchingElements', FILTER_VALIDATE_INT) ?: 0;
    }

    /**
     * Resets all block counters to an empty state.
     */
    public function resetCounters() {
        $this->counter = [];
    }

    /**
     * @param string $name
     * @return int
     */
    public function getCounter($name) {
        return $this->counter[$name] ?? 0;
    }

    /**
     * @param string $name
     */
    public function incrementCounter($name) {
        $this->counter[$name] ??= $this->getInitialCounter();
        $this->counter[$name]++;
    }

    /**
     * @param int $blockId
     * @return void
     */
    public function addBlock($blockId)
    {
        $post = get_post($blockId);

        /** @var \WP_Post|null */
        $post = $post;

        // Could be referencing a non-existing block from another site.
        if (!isset($post)) return;

        $title = empty($post->post_title) ?
            'Block #' . $blockId :
            $post->post_title;

        // Ignore duplicates
        if (!isset($this->blocks[$blockId])) {
            $this->blocks[$blockId] = html_entity_decode($title);
        }
    }

    /**
     * @return array<int, string>
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}

function resetGlobalBlockCounters() {
    BlockCounterManager::getInstance()->resetCounters();
}

/**
 * @param int $blockId
 * @param int|null $repeaterItemNodeId
 * @return string|false
 * @throws \Exception
 */
function renderGlobalBlock($blockId, $repeaterItemNodeId = null)
{
    $manager = BlockCounterManager::getInstance();

    $blockUniqueId = ($repeaterItemNodeId ? $repeaterItemNodeId . "-" : "") . $blockId;

    $manager->incrementCounter($blockUniqueId);
    $currentIndex = $manager->getCounter($blockUniqueId);

    $manager->addBlock($blockId);

    $blockUniqueId .= '-' . $currentIndex;

    /**
     * @psalm-suppress InvalidScalarArgument
     */
    return $blockId ? \Breakdance\Render\render($blockId, $blockUniqueId) : false;
}
