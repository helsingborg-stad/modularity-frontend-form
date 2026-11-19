class FieldValidatorUIHandler implements FieldValidatorUIHandlerInterface {
	private static nextNoticeId = 0;
	private describedInputs: Element[] = [];
	private messageElementTarget: string =
		"[data-js-frontend-form-notice-message]";
	private field!: FieldInterface;
	private activeNotice: HTMLElement | null = null;
	private invalidClass: string = "is-invalid";
	private currentNoticeMessage: string | null = null;
	private noticeMessageElement: HTMLElement | null = null;
	private errorNotice!: HTMLElement;

	constructor(private notices: NoticeInterface) {}

	public init(field: FieldInterface) {
		this.errorNotice = this.notices.getErrorNotice();
		this.noticeMessageElement = this.errorNotice.querySelector(
			this.messageElementTarget,
		);
		this.field = field;
	}

	public addInvalidNotice(message: string): void {
		if (this.activeNotice) {
			return;
		}

		const messageElement = this.errorNotice.querySelector(
			this.messageElementTarget,
		);

		if (!messageElement) {
			console.error("Message element not found in notice");
			return;
		}

		this.noticeMessageElement = messageElement as HTMLElement;
		this.currentNoticeMessage = message;
		this.noticeMessageElement.textContent = message;
		this.field.getFieldContainer().classList.add(this.invalidClass);
		this.field.getFieldContainer().append(this.errorNotice);
		this.activeNotice = this.errorNotice;
		this.errorNotice.id = `frontend-form-error-${++FieldValidatorUIHandler.nextNoticeId}`;
		this.errorNotice.setAttribute("role", "alert");
		this.describedInputs = Array.from(this.field.getFieldContainer().querySelectorAll("input, select, textarea, [contenteditable=true]"))
			.filter(input => input.closest("[data-js-field]") === this.field.getFieldContainer());
		for (const input of this.describedInputs) {
			const descriptions = input.getAttribute("aria-describedby")?.split(/\s+/).filter(Boolean) ?? [];
			input.setAttribute("aria-describedby", [...descriptions, this.errorNotice.id].join(" "));
			input.setAttribute("aria-invalid", "true");
		}
	}

	public updateInvalidNotice(message: string): void {
		if (!this.activeNotice || this.currentNoticeMessage === message) {
			return;
		}

		this.noticeMessageElement!.textContent = message;
		this.currentNoticeMessage = message;
	}

	public removeInvalidNotice(): void {
		if (this.activeNotice) {
			this.field.getFieldContainer().classList.remove(this.invalidClass);
			for (const input of this.describedInputs) {
				const descriptions = (input.getAttribute("aria-describedby") ?? "").split(/\s+/)
					.filter(id => id && id !== this.errorNotice.id);
				if (descriptions.length) input.setAttribute("aria-describedby", descriptions.join(" "));
				else input.removeAttribute("aria-describedby");
				input.removeAttribute("aria-invalid");
			}
			this.describedInputs = [];
			this.activeNotice.remove();
			this.activeNotice = null;
		}
	}
}

export default FieldValidatorUIHandler;
