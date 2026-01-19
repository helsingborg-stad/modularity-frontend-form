type ProgressbarOptions = {
  behavior?: ScrollBehavior;
};

export class FormProgressBar {
  private root: HTMLElement;
  private options: Required<ProgressbarOptions>;
  private observer?: MutationObserver;

  constructor(root: HTMLElement, options: ProgressbarOptions = {}) {
    this.root = root;
    this.options = {
      behavior: options.behavior ?? 'smooth',
    };

    this.addInitedFlag();
    this.centerActiveStep();
    this.observeActiveChanges();
  }

  /**   
   * Add initialized flag to communicate initialization
   */
  private addInitedFlag(): void {
    this.root.setAttribute('data-frontend-form-progressbar-inited', 'true');
  }

  /**
   * Find and center/focus the active step
   */
  /**
   * Centers the currently active step within the progress bar container,
   * either vertically or horizontally depending on the layout direction.
   *
   * - In vertical mode (when the closest `.mod-frontend-form__layout` has `flex-direction: row`),
   *   it scrolls the container vertically so the active step is centered.
   * - In horizontal mode (default), it scrolls the container horizontally to center the active step.
   *
   * The method also sets focus to the active step for accessibility.
   *
   * @remarks
   * For this method to have a visible effect, the progress bar container (`this.root`)
   * must have scrolling enabled in the relevant direction (i.e., `overflow-y: auto` for vertical,
   * `overflow-x: auto` for horizontal) and its content must overflow the container's bounds.
   */
  private centerActiveStep(): void {
    const activeStep = this.root.querySelector<HTMLElement>(
      '.mod-frontend-form__progressbar-step.is-active'
    );

    if (!activeStep) {
      return;
    }

    // Detect vertical mode using computed style (e.g., flex-direction)
    const layoutElement       = this.root.closest<HTMLElement>('.mod-frontend-form__layout');
    const layoutComputedStyle = layoutElement ? window.getComputedStyle(layoutElement) : undefined;

    const isVertical = layoutComputedStyle
      ? layoutComputedStyle.flexDirection === 'row'
      : false;

      console.log('Centering active step in', isVertical ? 'vertical' : 'horizontal', 'mode.');

    if (isVertical) {
      // Scroll so the active step is centered vertically
      const containerRect = this.root.getBoundingClientRect();
      const stepRect      = activeStep.getBoundingClientRect();
      const offset =
        stepRect.top -
        containerRect.top -
        containerRect.height / 2 +
        stepRect.height / 2;

      this.root.scrollTop += offset;

      activeStep.setAttribute('tabindex', '-1');
      activeStep.focus();
    } else {
      // Horizontal (default) behavior
      const containerRect = this.root.getBoundingClientRect();
      const stepRect      = activeStep.getBoundingClientRect();
      const offset =
        stepRect.left -
        containerRect.left -
        containerRect.width / 2 +
        stepRect.width / 2;
      this.root.scrollLeft += offset;
    }
  }

  /**
   * Observe class changes so we recenter when is-active moves
   */
  private observeActiveChanges(): void {
    this.observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (
          mutation.type === 'attributes' &&
          mutation.attributeName === 'class'
        ) {
          const target = mutation.target as HTMLElement;

          console.log('Mutation observed on progressbar step:', target);

          if (
            target.classList.contains('mod-frontend-form__progressbar-step') &&
            target.classList.contains('is-active')
          ) {
            this.centerActiveStep();
            break;
          }
        }
      }
    });

    this.observer.observe(this.root, {
      subtree: true,
      attributes: true,
      attributeFilter: ['class'],
    });
  }

  /**
   * Cleanup (optional but correct)
   */
  public destroy(): void {
    this.observer?.disconnect();
  }

  /**
   * Static initializer for all progressbars on page
   */
  static initAll(options?: ProgressbarOptions): FormProgressBar[] {
    return Array.from(
      document.querySelectorAll<HTMLElement>('[data-js-frontend-form-progressbar]')
    ).map((el) => new FormProgressBar(el, options));
  }
}

export default FormProgressBar;
