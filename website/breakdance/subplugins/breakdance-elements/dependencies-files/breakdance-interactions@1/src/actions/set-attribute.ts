import { Action, PossibleEvent } from "../types";
import { replaceRandomVariable } from "../utils";

export function setAttribute(event: PossibleEvent, target: HTMLElement, action?: Action) {
  if (!action?.attribute_name) return;
  const value = replaceRandomVariable(action.attribute_value || "");
  target.setAttribute(action.attribute_name, value);
}