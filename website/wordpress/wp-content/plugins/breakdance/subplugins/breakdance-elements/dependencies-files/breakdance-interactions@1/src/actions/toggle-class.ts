import { Action, PossibleEvent } from "../types";
import { cleanClassName } from "../utils";

export function toggleClass(event: PossibleEvent, target: HTMLElement, action?: Action) {
  if (!action?.css_class) return;

  const className = cleanClassName(action.css_class);
  target.classList.toggle(className);
}