declare global {
  interface WindowEventMap {
    breakdance_refresh_interactions: CustomEvent
  }

  interface Window {
    BreakdanceFrontend: Record<string, any>
  }
}

export type Nullable<T> = { [K in keyof T]: T[K] | null };

export type PossibleEvent = Event | MouseEvent | KeyboardEvent | TouchEvent | IntersectionObserverEntry | undefined;

export type Target = "custom" | "this_element" | "target" | "parent";
export type Match = "everywhere" | "limit_to_this_element" | "closest_parent" | "children_of_target";

export type Interaction = Partial<
  Nullable<{
    trigger: string;
    target: Target;
    css_selector: string;
    match: Match
    actions: Action[];

    key: string;
    ctrl_key: boolean;
    shift_key: boolean;
  }>
>;

export type Action = Partial<
  Nullable<{
    name: string;
    target: Target;
    css_selector: string;
    match: Match;
    advanced: Nullable<{
      run_only_once: boolean;
      disable_at: string[];
    }>;

    css_class: string;
    display: string;
    variable_name: string;
    variable_value: string;
    attribute_name: string;
    attribute_value: string;
    scroll_offset: number;
    scroll_delay: { number: number|null; style: string, unit: string };
    js_function_name: string;
    threshold: number;
  }>
>;

export type Listener = {
  node: HTMLElement;
  fn: (event: any) => void;
};