import { associatePhoneError, clearPhoneError } from './phoneValidation';

describe('native ACF phone error accessibility', () => {
	it('associates and clears errors without losing instructions', () => {
		document.body.innerHTML = `<div data-phone-validation="true"><input type="text" id="phone" aria-describedby="instructions"><div class="acf-error-message">Check the number</div></div>`;
		const container = document.body.firstElementChild as HTMLElement;
		const input = container.querySelector('input')!;
		associatePhoneError(container);
		expect(input.getAttribute('aria-invalid')).toBe('true');
		expect(input.getAttribute('aria-describedby')).toBe('instructions phone-phone-error');
		expect(document.getElementById('phone-phone-error')?.textContent).toBe('Check the number');
		associatePhoneError(container);
		expect(input.getAttribute('aria-describedby')).toBe('instructions phone-phone-error');
		clearPhoneError(container);
		expect(input.hasAttribute('aria-invalid')).toBe(false);
		expect(input.getAttribute('aria-describedby')).toBe('instructions');
	});

	it('leaves ordinary text fields unchanged', () => {
		document.body.innerHTML = '<div><input type="text" id="plain"><div class="acf-error-message">Required</div></div>';
		associatePhoneError(document.body.firstElementChild as HTMLElement);
		expect(document.querySelector('input')?.hasAttribute('aria-invalid')).toBe(false);
	});
});
