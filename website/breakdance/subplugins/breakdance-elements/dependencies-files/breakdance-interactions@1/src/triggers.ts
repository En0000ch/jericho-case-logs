import BreakdanceActions from "./actions";
import { Action, Interaction, Listener, PossibleEvent } from "./types";
import { isNodeSameOrAncestor } from "./utils";

export default class BreakdanceTriggers {
  private actions = new BreakdanceActions();

  listeners: Map<string, Listener[]> = new Map([]);

  private triggerMap = new Map([
    ["click", this.handleClick],
    ["mouse_enter", this.handleMouseEnter],
    ["mouse_leave", this.handleMouseLeave],
    ["scroll_into_view", this.handleScrollIntoView],
    ["scroll_out_of_view", this.handleScrollOutOfView],
    ["page_loaded", this.handlePageLoaded],
    ["page_scrolled", this.handlePageScrolled],
    ["key_down", this.handleKeyDown],
    ["key_up", this.handleKeyUp]
  ]);

  constructor() {
    ["click", "mouseenter", "mouseleave", "scroll", "keydown", "keyup"]
      .forEach(event => this.listen(event));
  }

  getTriggerByName(triggerName: string) {
    const trigger = this.triggerMap.get(triggerName);

    if (!trigger) {
      console.error(`Trigger ${triggerName} not found.`);
      return null;
    }

    return trigger.bind(this);
  }

  on(name: string, listener: Listener) {
    const listeners = this.listeners.get(name) || [];
    listeners.push(listener);
    this.listeners.set(name, listeners);
  }

  off(name: string, listener: Listener) {
    const listeners = this.listeners.get(name) || [];
    this.listeners.set(name, listeners.filter(l => l.fn !== listener.fn));
  }

  listen(eventName: string) {
    const isPageEvent = ["scroll"].includes(eventName);

    document.addEventListener(eventName, event => {
      const listeners = this.listeners.get(eventName) || [];
      if (!listeners.length) return;

      listeners.forEach(({ node, fn }) => {
        if (!isPageEvent && !isNodeSameOrAncestor(node, event.target as HTMLElement)) {
          return;
        }
        fn(event);
      });
    }, { capture: true });
  }

  handleClick(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let clicked = false;

    const clickFn = (event: MouseEvent) => {
      if (action?.advanced?.run_only_once && clicked) return;

      this.actions.runAction(
        event,
        targetNode,
        sourceNode,
        action
      );

      clicked = true;
    };

    this.on("click", {
      node: targetNode,
      fn: clickFn
    });

    return () => {
      this.off("click", {
        node: targetNode,
        fn: clickFn
      });
    };
  }

  handleMouseEnter(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let mouseEntered = false;

    const enterFn = (event: MouseEvent) => {
      if (action?.advanced?.run_only_once && mouseEntered) return;

      this.actions.runAction(
        event,
        targetNode,
        sourceNode,
        action
      );

      mouseEntered = true;
    };

    this.on("mouseenter", {
      node: targetNode,
      fn: enterFn
    });

    return () => {
      this.off("mouseenter", {
        node: targetNode,
        fn: enterFn
      });
    }
  }

  handleMouseLeave(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let mouseLeft = false;

    const leaveFn = (event: MouseEvent) => {
      if (action?.advanced?.run_only_once && mouseLeft) return;

      this.actions.runAction(
        event,
        targetNode,
        sourceNode,
        action
      );

      mouseLeft = true;
    }

    this.on("mouseleave", {
      node: targetNode,
      fn: leaveFn
    });

    return () => {
      this.off("mouseleave", {
        node: targetNode,
        fn: leaveFn
      });
    }
  }

  handleScrollIntoView(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let scrolledIntoViewOnce = false;

    const options = {
      threshold: action.threshold || 0.5
    };

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.actions.runAction(
            entry,
            targetNode,
            sourceNode,
            action
          );

          scrolledIntoViewOnce = true;
        }

        if (action?.advanced?.run_only_once && scrolledIntoViewOnce) {
          observer.disconnect();
        }
      });
    }, options);

    observer.observe(targetNode);

    return () => {
      observer.disconnect();
    };
  }

  handleScrollOutOfView(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let scrolledOutOfViewOnce = false;

    const options = {
      threshold: action.threshold || 0.5
    };

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) {
          this.actions.runAction(
            entry,
            targetNode,
            sourceNode,
            action
          );

          scrolledOutOfViewOnce = true;
        }

        if (action?.advanced?.run_only_once && scrolledOutOfViewOnce) {
          observer.disconnect();
        }
      });
    }, options);

    observer.observe(targetNode);

    return () => {
      observer.disconnect();
    };
  }

  handlePageLoaded(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    // The page loaded event does not need a target node
    this.actions.runAction(
      undefined,
      sourceNode,
      sourceNode,
      action
    );

    return () => {};
  }

  handlePageScrolled(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement
  ) {
    let lastScroll = 0;
    let scrolledDownOnce = false;

    const scrollFn = (event: Event) => {
      if (action?.advanced?.run_only_once && scrolledDownOnce) {
        return;
      }

      if (window.scrollY > lastScroll) {
        this.actions.runAction(
          event,
          targetNode,
          sourceNode,
          action
        );

        scrolledDownOnce = true;
      }

      lastScroll = window.scrollY;
    };

    this.on("scroll", {
      node: targetNode,
      fn: scrollFn
    });

    return () => {
      this.off("scroll", {
        node: targetNode,
        fn: scrollFn
      });
    };
  }

  handleKeyDown(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement,
    trigger: Interaction
  ) {
    let keyDownOnce = false;
    const isShiftNeeded = trigger.shift_key;
    const isCtrlNeeded = trigger.ctrl_key;

    const keyDownFn = (event: KeyboardEvent) => {
      const isSameKey = event.key.toLowerCase() === trigger.key?.toLowerCase();

      if (isShiftNeeded && !event.shiftKey) return;
      if (isCtrlNeeded && !event.ctrlKey) return;
      if (action?.advanced?.run_only_once && keyDownOnce) return;

      if (!trigger.key || isSameKey) {
        this.actions.runAction(
          event,
          targetNode,
          sourceNode,
          action
        );

        keyDownOnce = true;
      }
    };

    this.on("keydown", {
      node: targetNode,
      fn: keyDownFn
    });

    return () => {
      this.off("keydown", {
        node: targetNode,
        fn: keyDownFn
      });
    };
  }

  handleKeyUp(
    action: Action,
    sourceNode: HTMLElement,
    targetNode: HTMLElement,
    trigger: Interaction
  ) {
    let keyUpOnce = false;
    const isShiftNeeded = trigger.shift_key;
    const isCtrlNeeded = trigger.ctrl_key;

    const keyUpFn = (event: KeyboardEvent) => {
      const isSameKey = event.key.toLowerCase() === trigger.key?.toLowerCase();

      if (isShiftNeeded && !event.shiftKey) return;
      if (isCtrlNeeded && !event.ctrlKey) return;
      if (action?.advanced?.run_only_once && keyUpOnce) return;

      if (!trigger.key || isSameKey) {
        this.actions.runAction(
          event,
          targetNode,
          sourceNode,
          action
        );

        keyUpOnce = true;
      }
    };

    this.on("keyup", {
      node: targetNode,
      fn: keyUpFn
    });

    return () => {
      this.off("keyup", {
        node: targetNode,
        fn: keyUpFn
      });
    };
  }
}