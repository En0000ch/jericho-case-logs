import { Action, PossibleEvent } from "../types";

export function jsFunction(event: PossibleEvent, target: HTMLElement, action?: Action) {
  if (!action?.js_function_name) return;

  const maybeFn = (window as any)[action.js_function_name];

  if (!maybeFn) {
    console.log(`[INTERACTIONS] Function ${action.js_function_name} not found.`);
    return;
  }

  maybeFn(event, target, action);
}