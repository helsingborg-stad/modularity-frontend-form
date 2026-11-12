import Notice from "../fields/notice/notice";

/** First server error only; navigation remains with the existing form controls. */
export default class FieldError {
	private cleanup: (() => void) | null = null;
	private static nextId = 0;

	constructor(private form: HTMLFormElement, private container: HTMLElement) {}

	clear(): void {
		this.cleanup?.();
		this.cleanup = null;
	}

	show(inputName: unknown, message: unknown): boolean {
		this.clear();
		if (typeof inputName !== "string" || typeof message !== "string") return false;
		const input = Array.from(this.form.elements).find(element =>
			(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement)
			&& (element.name === inputName || element.name === `${inputName}[]`) && !element.disabled
		) as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | undefined;
		const field = input?.closest<HTMLElement>("[data-js-field-name]");
		if (!input || !field) return false;

		const notice = new Notice(this.container).getErrorNotice();
		const text = notice.querySelector<HTMLElement>("[data-js-frontend-form-notice-message]");
		if (!text) return false;
		text.textContent = message;
		notice.id = `frontend-form-server-error-${++FieldError.nextId}`;
		notice.setAttribute("role", "alert");
		const invalid = input.getAttribute("aria-invalid");
		const describedBy = input.getAttribute("aria-describedby");
		input.setAttribute("aria-invalid", "true");
		input.setAttribute("aria-describedby", [describedBy, notice.id].filter(Boolean).join(" "));
		field.append(notice);
		this.cleanup = () => {
			notice.remove();
			if (invalid === null) input.removeAttribute("aria-invalid");
			else input.setAttribute("aria-invalid", invalid);
			const descriptions = (input.getAttribute("aria-describedby") || "").split(/\s+/).filter(id => id && id !== notice.id);
			if (descriptions.length) input.setAttribute("aria-describedby", descriptions.join(" "));
			else input.removeAttribute("aria-describedby");
			field.removeEventListener("input", clear);
			field.removeEventListener("change", clear);
		};
		const clear = () => this.clear();
		field.addEventListener("input", clear);
		field.addEventListener("change", clear);
		return true;
	}
}
