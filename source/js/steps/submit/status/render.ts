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

  constructor(private formContainer: HTMLElement) {}

  /**
   * Set up the event listener for submit status changes.
   */
  public setup(): void {
    this.formContainer.addEventListener('submitStatusChanged', (event: Event) => {
      const { status, message, icon, progress, delay = 400 } = (event as CustomEvent).detail;

      this.messageQueue.push({ status, message, icon, progress, delay});

      if (!this.isProcessing) {
        const workingElement = this.formContainer.querySelector('[data-js-frontend-form-working]') as HTMLElement;
        if (workingElement) {
          workingElement.classList.remove('u-display--none');
          workingElement.style.opacity = '0';
          setTimeout(() => {
            workingElement.style.opacity = '1';
          }, 10);
        }
        this.processQueue();
      }
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

        //Hide the working element
        const workingElement = this.formContainer.querySelector('[data-js-frontend-form-working]') as HTMLElement;
        if (workingElement) {
          workingElement.style.opacity = '0';
          setTimeout(() => {
            workingElement.classList.add('u-display--none');
          }, 1000);
        }
      }, 300);

      return;
    }

    this.isProcessing = true;

    const { status, message, icon, progress, delay } = this.messageQueue.shift()!;

    // Remove existing status classes
    this.formContainer.classList.remove(...this.statusClasses);

    // Add the new status class
    this.formContainer.classList.add(`is-${status}`);

    // Update progress bar
    const progressContainer = this.formContainer.querySelector('[data-js-frontend-form-working__progress] .c-progressbar__value') as HTMLElement;
    if (progressContainer) {
      progressContainer.style.width = `${progress}%`;
    }

    // Update description
    const descriptionEl = this.formContainer.querySelector('[data-js-frontend-form-working__description]') as HTMLElement;
    if (descriptionEl) {
      descriptionEl.textContent = (progress > 0 ? `${message} (${progress}%)` : message);
    }

    // Set the icon with fade-out and fade-in effect, only if it changed
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

    // Wait for the delay before processing the next message
    setTimeout(() => {
      this.processQueue();
    }, delay);
  }
}

export default SubmitStatusRenderer;