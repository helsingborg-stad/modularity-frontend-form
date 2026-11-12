import FieldError from './fieldError';

describe('server field errors', () => {
	beforeEach(() => {
		document.body.innerHTML = `<div id="container">
		<template data-js-frontend-form-error-notice><span data-js-frontend-form-notice-message></span></template>
		<template data-js-frontend-form-success-notice></template>
		<form><div data-js-field-name="row0"><input type="tel" name="form[rows][0][phone]" value="0047 406 12 345"></div>
		<div data-js-field-name="row1"><input type="tel" name="form[rows][1][phone]" value="bad" aria-describedby="instructions"></div></form></div>`;
	});

	it('targets the exact repeater row, renders text and clears on correction without losing instructions or values', () => {
		const form = document.querySelector('form')!;
		const errors = new FieldError(form, document.querySelector('#container')!);
		const inputs = form.querySelectorAll('input');
		expect(errors.show(inputs[1].name, '<b>Use + or 00</b>')).toBe(true);
		expect(inputs[0].hasAttribute('aria-invalid')).toBe(false);
		expect(inputs[1].getAttribute('aria-invalid')).toBe('true');
		const notice = form.querySelector('[role="alert"]')!;
		expect(notice.textContent).toBe('<b>Use + or 00</b>');
		expect(notice.querySelector('b')).toBeNull();
		expect(inputs[1].getAttribute('aria-describedby')).toContain(notice.id);
		inputs[1].value = '070-123 45 67';
		inputs[1].dispatchEvent(new Event('input', { bubbles: true }));
		expect(form.querySelector('[role="alert"]')).toBeNull();
		expect(inputs[1].getAttribute('aria-describedby')).toBe('instructions');
		expect(inputs[1].hasAttribute('aria-invalid')).toBe(false);
		expect(inputs[0].value).toBe('0047 406 12 345');
	});

	it.each(['checkbox', 'radio'])('targets a %s group and clears when another option changes', (type) => {
		const form = document.querySelector('form')!;
		const name = `form[choices]${type === 'checkbox' ? '[]' : ''}`;
		form.innerHTML = `<fieldset data-js-field-name="choices">
			<input type="${type}" name="${name}" value="one">
			<input type="${type}" name="${name}" value="two">
		</fieldset>`;
		const errors = new FieldError(form, document.querySelector('#container')!);
		expect(errors.show('form[choices]', 'Choose an option')).toBe(true);
		expect(form.querySelector('[role="alert"]')).not.toBeNull();
		form.querySelectorAll('input')[1].dispatchEvent(new Event('change', { bubbles: true }));
		expect(form.querySelector('[role="alert"]')).toBeNull();
		expect(form.querySelector('[aria-invalid]')).toBeNull();
	});

	it('cleans up before revalidation and falls back for unresolved or disabled fields', () => {
		const form = document.querySelector('form')!;
		const errors = new FieldError(form, document.querySelector('#container')!);
		const input = form.querySelector('input')!;
		errors.show(input.name, 'Error');
		errors.clear();
		expect(form.querySelector('[role="alert"]')).toBeNull();
		expect(errors.show('missing', 'Error')).toBe(false);
		input.disabled = true;
		expect(errors.show(input.name, 'Error')).toBe(false);
		expect(errors.show({}, 'Error')).toBe(false);
	});
});
