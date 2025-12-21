import BreakdanceTriggers from "./triggers";
import { Action, Interaction } from "./types";
import {
  noop,
  getInteractionsFromNode,
  getTargetList,
  getNodeUniqueSelector,
  replaceVariablesInCssSelector
} from "./utils";

const triggers = new BreakdanceTriggers();
const subscriptions: Map<HTMLElement, (() => void)[]> = new Map();

function getTriggerFn(trigger: string) {
  // Check if trigger is built-in, otherwise check if a global function exists with the same name.
  return triggers.getTriggerByName(trigger) || (window as any)[trigger] || noop;
}

function refreshInteractions(event: CustomEvent) {
  const target = event.target as HTMLElement;

  if (subscriptions.has(target)) {
    subscriptions.get(target)
      ?.filter(Boolean)
      ?.forEach(unsubscribe => unsubscribe());

    subscriptions.delete(target);
  }

  attachBehaviorToNode(target);
}

function initInteraction(sourceNode: HTMLElement, interaction: Interaction) {
  const {
    trigger,
    target,
    css_selector: cssSelector,
    match,
    actions
  } = interaction;

  if (!trigger) return;

  const triggerTargets = getTargetList(
    sourceNode,
    sourceNode, // On purpose - targets only exist in actions.
    target,
    cssSelector,
    match
  );

  const triggerFn = getTriggerFn(trigger);

  triggerTargets.forEach(triggerTargetNode => {
    const prevSubs = subscriptions.get(sourceNode) || [];
    const newSubs = [ ...prevSubs ];

    actions?.forEach((action) => {
      const sub = triggerFn(action, sourceNode, triggerTargetNode, interaction);
      newSubs.push(sub);
    });

    subscriptions.set(sourceNode, newSubs);
  });
}

function attachBehaviorToNode(node: HTMLElement) {
  const interactions = getInteractionsFromNode(node);
  interactions.forEach(interaction => initInteraction(node, interaction));
}

function autoload() {
  const nodes: HTMLElement[] = Array.from(
    document.querySelectorAll("[data-interactions]")
  );

  nodes.forEach(node => attachBehaviorToNode(node));
}

addEventListener("breakdance_refresh_interactions", refreshInteractions);
document.addEventListener('DOMContentLoaded', () => autoload());