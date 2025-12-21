import { Action, PossibleEvent } from "../types";

export function removeAttribute(event: PossibleEvent, target: HTMLElement, action?: Action) {
  if (!action?.attribute_name) return;
  target.removeAttribute(action.attribute_name);
}