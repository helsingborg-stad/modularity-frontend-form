import { SubmitStatus } from "./enum";

class StatusRendererMessageUI {
	constructor(
		private modularityFrontendFormLang: ModularityFrontendFormLang,
		private icon: HTMLElement,
		private title: HTMLElement,
		private description: HTMLElement,
		private progressbar: HTMLElement,
	) {}

	/**
	 * Changes the iconName with transition effect.
	 */
	public updateIcon(iconName: string): void {
		const currentIcon = this.icon.getAttribute("data-material-symbol") || "";
		const [currentBase] = currentIcon.split("_");
		const [newBase] = iconName.split("_");
		if (currentBase !== newBase) {
			this.icon.style.transition = "opacity 0.3s";
			this.icon.style.opacity = "0";
			setTimeout(() => {
				this.icon.setAttribute("data-material-symbol", iconName);
				this.icon.style.opacity = "1";
			}, 300);
		} else {
			this.icon.setAttribute("data-material-symbol", iconName);
		}
	}

	/**
	 * Updates the title based on the status.
	 */
	public updateTitle(status: string): void {
		const statusTitles: Record<SubmitStatus, string> = {
			[SubmitStatus.Loading]:
				this.modularityFrontendFormLang?.statusTitleLoading ?? "Loading",
			[SubmitStatus.Success]:
				this.modularityFrontendFormLang?.statusTitleSucess ?? "Success",
			[SubmitStatus.Error]:
				this.modularityFrontendFormLang?.statusTitleError ?? "Error",
			[SubmitStatus.Working]:
				this.modularityFrontendFormLang?.statusTitleSubmitting ?? "Submitting",
		};

		this.title.textContent = statusTitles[status as SubmitStatus] ?? "";
	}

	/**
	 * Updates the descriptive text.
	 */
	public updateDescription(message: string, progress: number): void {
		this.description.textContent =
			progress > 0 ? `${message} (${progress}%)` : message;
	}

	/**
	 * Updates the progress bar width.
	 */
	public updateProgressBar(progress: number): void {
		this.progressbar.style.width = `${progress}%`;
	}
}

export default StatusRendererMessageUI;
