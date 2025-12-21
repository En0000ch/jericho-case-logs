import { Action, PossibleEvent } from "../types";

export function show(event: PossibleEvent, target: HTMLElement, action?: Action) {
  if (target.style.display === 'none') {
    target.style.display = '';
  }

  // If the element is hidden via CSS, we need to set the display property.
  const display = getComputedStyle(target).display;

  if (display === 'none') {
    target.style.display = action?.display || 'block';
  }
}