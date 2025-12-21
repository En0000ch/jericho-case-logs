import { Action, PossibleEvent } from "./types";

import { addClass } from "./actions/add-class";
import { removeClass } from "./actions/remove-class";
import { toggleClass } from "./actions/toggle-class";
import { hide } from "./actions/hide";
import { show } from "./actions/show";
import { setVariable } from "./actions/set-variable";
import { setAttribute } from "./actions/set-attribute";
import { removeAttribute } from "./actions/remove-attribute";
import { _scrollTo } from "./actions/scroll-to";
import { jsFunction } from "./actions/js-function";
import { getTargetList, isActionDisabled } from "./utils";
import { focus } from "./actions/focus";
import { blur } from "./actions/blur";

export default class BreakdanceActions {
  private actionMap = new Map([
    ["add_class", addClass],
    ["remove_class", removeClass],
    ["toggle_class", toggleClass],
    ["hide_element", hide],
    ["show_element", show],
    ["set_variable", setVariable],
    ["set_attribute", setAttribute],
    ["remove_attribute", removeAttribute],
    ["scroll_to", _scrollTo],
    ["focus", focus],
    ["blur", blur],
    ["javascript_function", jsFunction],
  ]);

  getActionByName(actionName: string) {
    const action = this.actionMap.get(actionName);

    if (!action) {
      console.error(`Action ${actionName} not found.`);
      return null;
    }

    return action.bind(this); // Bind this context to ensure methods work correctly
  }

  runAction(
    event: PossibleEvent,
    targetNode: HTMLElement,
    sourceNode: HTMLElement,
    action: Action
  ) {
    if (isActionDisabled(action)) return;

    const fn = this.getActionByName(action.name!);

    if (!fn) return;

    const targets = getTargetList(
      sourceNode,
      targetNode,
      action.target,
      action.css_selector,
      action.match
    );

    targets.forEach((target) => {
      console.debug(
        "Running action:", action.name, `on element "${target.classList[0]}"`,
        action
      );

      fn(event, target, action);
    });
  }
}