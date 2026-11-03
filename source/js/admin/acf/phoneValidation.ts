type NativeField = { $el: { get(index: number): HTMLElement } };
declare const acf: { addAction(name: string, callback: (field: NativeField) => void): void };

const cleanup = new WeakMap<HTMLElement, () => void>();

export function clearPhoneError(container: HTMLElement): void {
	cleanup.get(container)?.();
	cleanup.delete(container);
}

export function associatePhoneError(container: HTMLElement): void {
	clearPhoneError(container);
	if (!container.hasAttribute('data-phone-validation')) return;
	const input = container.querySelector<HTMLInputElement>('input[type="text"]');
	const error = container.querySelector<HTMLElement>('.acf-error-message');
	if (!input || !error) return;
	const invalid = input.getAttribute('aria-invalid');
	const originalId = error.id;
	error.id ||= `${input.id}-phone-error`;
	const errorId = error.id;
	const describedBy = input.getAttribute('aria-describedby');
	input.setAttribute('aria-invalid', 'true');
	input.setAttribute('aria-describedby', [describedBy, errorId].filter(Boolean).join(' '));
	cleanup.set(container, () => {
		if (invalid === null) input.removeAttribute('aria-invalid');
		else input.setAttribute('aria-invalid', invalid);
		const remaining = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== errorId);
		if (remaining.length) input.setAttribute('aria-describedby', remaining.join(' '));
		else input.removeAttribute('aria-describedby');
		error.id = originalId;
	});
}

if (typeof acf !== 'undefined') {
	acf.addAction('invalid_field', field => associatePhoneError(field.$el.get(0)));
	acf.addAction('valid_field', field => clearPhoneError(field.$el.get(0)));
}
