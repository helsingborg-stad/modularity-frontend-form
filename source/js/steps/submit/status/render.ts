import SubmitStatus from './enum';
import SubmitStatusRendererInterface from './renderInterface';

class SubmitStatusRenderer implements SubmitStatusRendererInterface {
  private messageQueue: Array<{ status: string; message: string; icon: string; progress: number; delay: number }> = [];
  private isProcessing: boolean = false;
  private statusClasses: Array<string> = [
    'is-working',
    'is-success',
    'is-error'
  ];

  constructor(private formContainer: HTMLElement, private modularityFrontendFormLang: ModularityFrontendFormLang,) {}

  /**
   * Set up the event listener for submit status changes.
   */
  public setup(): void {
    this.formContainer.addEventListener('submitStatusChanged', (event: Event) => {
      this.handleStatusEvent(event);
    });
  }

  /**
   * Process the message queue.
   */
  private processQueue(): void {
    
    if (this.messageQueue.length === 0) {
      this.isProcessing = false;
      setTimeout(() => {
        this.formContainer.classList.remove(...this.statusClasses);
        this.hideWorkingElement();
      }, 300);
      return;
    }

    this.isProcessing = true;
    const { status, message, icon, progress, delay } = this.messageQueue.shift()!;
    
    this.applyStatusClass(status);
    this.updateProgressBar(progress);
    this.updateDescription(message, progress);
    this.updateTitle(status);
    this.updateIcon(icon);

    setTimeout(() => {
      this.processQueue();
    }, delay);
  }

  /**
   * Handles submit status event: extracts event details, queues, and shows working element if needed.
   */
  private handleStatusEvent(event: Event): void {
    const { status, message, icon, progress, delay = 200 } = (event as CustomEvent).detail;
    this.messageQueue.push({ status, message, icon, progress, delay });
    if (!this.isProcessing) {
      this.showWorkingElement();
      this.processQueue();
    }
  }

  /**
   * Shows the working element with fade-in animation.
   */
  private showWorkingElement(): void {
    const workingElement = this.formContainer.querySelector('[data-js-frontend-form-working]') as HTMLElement;
    if (workingElement) {
      workingElement.classList.remove('u-display--none');
      workingElement.style.opacity = '0';
      setTimeout(() => {
        workingElement.style.opacity = '1';
      }, 10);
    }
  }

  /**
   * Hides the working element with fade-out animation.
   */
  private hideWorkingElement(): void {
    const workingElement = this.formContainer.querySelector('[data-js-frontend-form-working]') as HTMLElement;
    if (workingElement) {
      workingElement.style.opacity = '0';
      setTimeout(() => {
        workingElement.classList.add('u-display--none');
      }, 1000);
    }
  }

  /**
   * Adds/removes appropriate status classes.
   */
  private applyStatusClass(status: string): void {
    this.formContainer.classList.remove(...this.statusClasses);
    this.formContainer.classList.add(`is-${status}`);
  }

  /**
   * Updates the progress bar width.
   */
  private updateProgressBar(progress: number): void {
    const progressContainer = this.formContainer.querySelector('[data-js-frontend-form-working__progress] .c-progressbar__value') as HTMLElement;
    if (progressContainer) {
      progressContainer.style.width = `${progress}%`;
    }
  }

  /**
   * Updates the descriptive text.
   */
  private updateDescription(message: string, progress: number): void {
    const descriptionEl = this.formContainer.querySelector('[data-js-frontend-form-working__description]') as HTMLElement;
    if (descriptionEl) {
      descriptionEl.textContent = (progress > 0 ? `${message} (${progress}%)` : message);
    }
  }

  /**
   * Updates the title based on the status.
   */
  private updateTitle(status: string): void {
    const titleEl = this.formContainer.querySelector('[data-js-frontend-form-working__title]') as HTMLElement;
    if (titleEl) {
      const statusTitles: Record<SubmitStatus, string> = {
        [SubmitStatus.Success]: this.modularityFrontendFormLang?.statusTitleSucess ?? 'Success',
        [SubmitStatus.Error]: this.modularityFrontendFormLang?.statusTitleError ?? 'Error',
        [SubmitStatus.Working]: this.modularityFrontendFormLang?.statusTitleSubmitting ?? 'Submitting'
      };
      titleEl.textContent = statusTitles[status as SubmitStatus] ?? '';
    }
  }

  /**
   * Changes the icon with transition effect.
   */
  private updateIcon(icon: string): void {
    const iconEl = this.formContainer.querySelector('[data-js-frontend-form-working__icon]') as HTMLElement;
    if (iconEl) {
      const currentIcon = iconEl.getAttribute('data-material-symbol') || '';
      const [currentBase] = currentIcon.split('_');
      const [newBase] = icon.split('_');
      if (currentBase !== newBase) {
        iconEl.style.transition = 'opacity 0.3s';
        iconEl.style.opacity = '0';
        setTimeout(() => {
          iconEl.setAttribute('data-material-symbol', icon);
          iconEl.style.opacity = '1';
        }, 300);
      } else {
        iconEl.setAttribute('data-material-symbol', icon);
      }
    }
  }
}

export default SubmitStatusRenderer;