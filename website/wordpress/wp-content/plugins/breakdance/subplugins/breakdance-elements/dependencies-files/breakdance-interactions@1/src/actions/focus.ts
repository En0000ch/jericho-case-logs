import { Action, PossibleEvent } from "../types";
import { cleanClassName } from "../utils";

export function focus(event: PossibleEvent, target: HTMLElement, action?: Action) {
  target.focus();
}