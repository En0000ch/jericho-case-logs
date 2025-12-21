import { Action, PossibleEvent } from "../types";

export function hide(event: PossibleEvent, target: HTMLElement, action?: Action) {
  target.style.display = 'none';
}