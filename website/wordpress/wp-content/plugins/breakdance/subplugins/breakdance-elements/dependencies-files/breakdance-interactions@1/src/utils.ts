import { Action, Match, Target } from "./types";

export function replaceRandomVariable(value: string) {
  const regex = /\{random:(-?\d+),(-?\d+)}/g;

  return value.replace(regex, (match: string, min: string, max: string) => {
    const minValue = parseInt(min);
    const maxValue = parseInt(max);
    // Ensure minValue is less than maxValue to avoid logic error in random number calculation
    const low = Math.min(minValue, maxValue);
    const high = Math.max(minValue, maxValue);
    // Generate a random number in the range [low, high]
    return Math.floor(Math.random() * (high - low + 1) + low).toString();
  });
}

export function replaceVariablesInCssSelector(
  value: string | null | undefined,
  variables: { [key: string]: string | ((param?: string) => string | number) }
): string | undefined {
  if (!value) return undefined;

  const regex = /\{(\w+)(?:\s+([^\}]+))?\}/g;

  return value.replace(regex, (match: string, key: string, param: string | undefined) => {
    const variableValue = variables[key];

    if (typeof variableValue === 'function') {
      // Call the function with the optional parameter if it's a function
      return `${variableValue(param)}`;
    }

    // If it's a string, return it directly or fall back to the original match if not found
    return variableValue || match;
  });
}

export function getAdminBarHeight() {
  const height = parseInt(getComputedStyle(document.documentElement)
    .getPropertyValue("--wp-admin--admin-bar--height"))

  if (isNaN(height)) return 0;

  return height;
}

export function getInteractionsFromNode(node: HTMLElement) {
  if (!node.dataset.interactions) {
    return [];
  }

  try {
    const interactions = JSON.parse(node.dataset.interactions);
    if (Array.isArray(interactions)) {
      return interactions;
    }
  } catch (error) {
    console.error("Error parsing interactions:", error);
  }

  return [];
}

export function getNodeIndex(node: HTMLElement) {
  const nodes = Array.from(node.parentElement?.children || []);
  return nodes.indexOf(node) + 1; // CSS is 1-based
}

export function getNodeUniqueSelector(node: HTMLElement) {
  // This works because `node` is always a Breakdance element with a unique class.
  return '.' + [...node.classList].join('.');
}

export function getMatchingNodes(
  sourceNode: HTMLElement,
  targetNode: HTMLElement,
  match?: Match | null,
  cssSelector?: string | null,
): HTMLElement[] {
  const parsedCssSelector = replaceVariablesInCssSelector(cssSelector, {
    index: `${getNodeIndex(targetNode)}`,
    parent_index: (selector) => {
      const parentNode = selector ?
        targetNode.closest(selector) as HTMLElement :
        targetNode.parentElement as HTMLElement;

      // TODO: Returning an empty string might create an invalid selector causing the action not to run,
      //  but it's better than creating the wrong selector and targeting an unrelated element.
      return parentNode ? getNodeIndex(parentNode) : '';
    }
  });

  if (!parsedCssSelector) return [];

  const getNodes = () => {
    switch (match) {
      case 'limit_to_this_element':
        return sourceNode.querySelectorAll(parsedCssSelector);

      case 'closest_parent': {
        const closestParent = cssSelector ? targetNode.closest(cssSelector) : null;
        return closestParent ? [closestParent] : [];
      }

      case 'children_of_target':
        return targetNode.querySelectorAll(parsedCssSelector);

      case 'everywhere':
      default:
        return document.querySelectorAll(parsedCssSelector);
    }
  };

  return Array.from(getNodes()) as HTMLElement[];
}

export function getTargetList(
  sourceNode: HTMLElement,
  targetNode: HTMLElement,
  targetType?: Target | null,
  cssSelector?: string | null,
  match?: Match | null,
) {
  if (targetType === "custom") {
    return getMatchingNodes(sourceNode, targetNode, match, cssSelector);
  }

  if (targetType === "target") {
    return [targetNode];
  }

  // Fallback to source element.
  return [sourceNode];
}

export function isNodeSameOrAncestor(node: HTMLElement, target: HTMLElement) {
  return node === target || node.contains(target);
}

export function isActionDisabled(action: Action) {
  if (!action.name) return true;

  const breakpoints = action.advanced?.disable_at || [];
  if (!breakpoints?.length) return false;

  const { getCurrentBreakpoint } = window.BreakdanceFrontend.utils;
  const currBreakpoint = getCurrentBreakpoint().id;
  return breakpoints.includes(currBreakpoint);
}

export function cleanClassName(str: string) {
  return str.startsWith('.') ? str.slice(1).trim() : str.trim();
}

export function noop() {}