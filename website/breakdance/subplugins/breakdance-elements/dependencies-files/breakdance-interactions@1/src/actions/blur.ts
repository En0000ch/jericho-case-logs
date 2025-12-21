import { Action, PossibleEvent } from "../types";
import { cleanClassName } from "../utils";

export function blur(event: PossibleEvent, target: HTMLElement, action?: Action) {
  target.blur();
}