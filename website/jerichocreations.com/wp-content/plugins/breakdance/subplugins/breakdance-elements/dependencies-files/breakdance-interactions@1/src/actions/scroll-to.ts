import { Action, PossibleEvent } from "../types";
import { getAdminBarHeight } from "../utils";

export function _scrollTo(event: PossibleEvent, target: HTMLElement, action?: Action) {
  const offset = action?.scroll_offset || 0;
  const delay = action?.scroll_delay || { number: 0, style: "0", unit: "ms" };
  const adminBarHeight = getAdminBarHeight();

  const num = delay.number || 0;
  const delayInMs = delay.unit === "s" ? num * 1000 : num;

  setTimeout(() => {
    const y = target.getBoundingClientRect().top + window.scrollY;

    window.scrollTo({
      top: y - offset - adminBarHeight,
      behavior: 'smooth'
    });
  }, delayInMs);
}