import { Action, PossibleEvent } from "../types";
import { replaceRandomVariable } from "../utils";

export function setVariable(event: PossibleEvent, target: HTMLElement, action?: Action) {
  const cleanedVariable = action?.variable_name?.replace('--', '');

  if (
    action?.variable_value === undefined ||
    action?.variable_value === null
  ) {
    target.style.removeProperty(`--${cleanedVariable}`);
    return;
  }

  const value = replaceRandomVariable(action.variable_value);
  target.style.setProperty(`--${cleanedVariable}`, value);
}