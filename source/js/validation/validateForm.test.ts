import ValidateForm from "./validateForm";
import userEvent from '@testing-library/user-event'
import { screen } from '@testing-library/dom';

const patternMessage = "Custom validation message";


/**
 * @jest-environment jsdom
 */
describe('validateForm', () => {
    it.each([
        [`<input type="text" name="testInput" pattern="^a" data-js-validation-message-pattern-mismatch="${patternMessage}"/>`, 'b'],
        // [`<select name="testInput" data-js-validation-message-value-missing="${patternMessage}" required value=""></select>`],
        [`<input type="text" name="testInput" data-js-validation-message-value-missing="${patternMessage}" required/>`, ''],
        [`<input name="testInput" data-js-validation-message-type-mismatch="${patternMessage}" type="email"/>`, 'invalid-email'],
        [`<input type="number" name="testInput" data-js-validation-message-range-overflow="${patternMessage}" max="5"/>`, '10'],
        [`<input type="number" name="testInput" data-js-validation-message-range-underflow="${patternMessage}" min="5"/>`, '4'],
        [`<input type="number" step="2" name="testInput" data-js-validation-message-step-mismatch="${patternMessage}"/>`, '3'],
        // [`<input type="number" name="testInput" data-js-validation-message-bad-input="${patternMessage}"/>`, 'a'],
    ])('allows for custom validation message using: %s', async (inputHtml: string, userInput: string) => {
        const user = userEvent.setup();
        document.body.innerHTML = `<form data-testid="testForm">${inputHtml}</form>`;
        const form: HTMLFormElement = screen.getByTestId('testForm');
        const input = form.querySelector('input[name="testInput"],select[name="testInput"]') as HTMLInputElement;
        new ValidateForm();
        await user.type(input, userInput);

        form.checkValidity();

        expect(input.validationMessage).toBe(patternMessage);
    });
})

// patternMismatch | Value doesn’t match the pattern attribute.
// typeMismatch | Value does not match the type (e.g., type="email" but not a valid email).
// customError | A custom error was set using setCustomValidity().
// valid | All constraints are satisfied.

// tooLong | Value exceeds maxlength.
// tooShort | Value is shorter than minlength.
// badInput | Typically for number inputs where the value can’t be parsed.
// stepMismatch | Number doesn’t match step increment.
