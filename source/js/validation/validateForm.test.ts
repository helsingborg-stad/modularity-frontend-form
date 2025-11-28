import ValidateForm from "./validateForm";
import userEvent from "@testing-library/user-event";
import { screen } from "@testing-library/dom";
import CustomInvalidValidationMessageItem from "./customInvalidValidationMessageItem";

const patternMessage = "Custom validation message";

/**
 * @jest-environment jsdom
 */
describe("validateForm", () => {
	it("applies custom validation messages for multiple scenarios", async () => {
		const user = userEvent.setup();

		const cases: [string, string | null][] = [
			[
				`<input type="text" name="testInput" pattern="^a" data-js-validation-message-pattern-mismatch="${patternMessage}"/>`,
				"b",
			],
			[
				`<select name="testInput" data-js-validation-message-value-missing="${patternMessage}" required value=""></select>`,
				null,
			],
			[
				`<input type="text" name="testInput" data-js-validation-message-value-missing="${patternMessage}" required/>`,
				null,
			],
			[
				`<input name="testInput" data-js-validation-message-type-mismatch="${patternMessage}" type="email"/>`,
				"invalid-email",
			],
			[
				`<input type="number" name="testInput" data-js-validation-message-range-overflow="${patternMessage}" max="5"/>`,
				"10",
			],
			[
				`<input type="number" name="testInput" data-js-validation-message-range-underflow="${patternMessage}" min="5"/>`,
				"4",
			],
			[
				`<input type="number" step="2" name="testInput" data-js-validation-message-step-mismatch="${patternMessage}"/>`,
				"3",
			],
		];

		for (const [inputHtml, userInput] of cases) {
			document.body.innerHTML = `<form data-testid="testForm">${inputHtml}</form>`;
			const form = screen.getByTestId("testForm") as HTMLFormElement;
			const input = form.querySelector(
				'input[name="testInput"],select[name="testInput"]',
			) as HTMLInputElement;
			new ValidateForm();

			if (userInput !== null) {
				await user.clear(input);
				await user.type(input, userInput);
			} else {
				input.focus();
				input.blur();
			}

			form.checkValidity();

			expect(input.validationMessage).toBe(patternMessage);
		}
	});

	it("filters out excluded validity keys", () => {
		const validateForm = new (ValidateForm as any)();
		const keys = validateForm.getValidationKeys();
		expect(keys).not.toContain("valid");
		expect(keys).not.toContain("constructor");
		expect(keys.length).toBeGreaterThan(0);
	});

	it("generates a valid query selector from validity keys", () => {
		const instance = new (ValidateForm as any)();
		const selector = instance.getTarget();
		expect(typeof selector).toBe("string");
		expect(selector).toContain("[data-js-validation-message-value-missing]");
	});

	it("caches and reuses validation message from dataset", () => {
		document.body.innerHTML = `<form>
            <input type="text" name="test" required data-js-validation-message-value-missing="Required once" />
        </form>`;

		const input = document.querySelector("input")!;
		const instance = new CustomInvalidValidationMessageItem(
			input,
			["valueMissing"],
			"jsValidationMessage",
		);

		input.dispatchEvent(new Event("invalid", { bubbles: true }));
		expect(input.validationMessage).toBe("Required once");

		delete input.dataset.jsValidationMessageValueMissing;

		input.dispatchEvent(new Event("invalid", { bubbles: true }));
		expect(input.validationMessage).toBe("Required once");
	});

	it("uses default validation message if no custom message is provided", () => {
		document.body.innerHTML = `<form>
            <input type="email" name="test" value="invalid" required />
        </form>`;

		const input = document.querySelector("input")!;
		input.type = "email"; // enforce email type mismatch
		input.value = "invalid"; // not a valid email
		const validationKeys = ["typeMismatch"];
		new CustomInvalidValidationMessageItem(
			input,
			validationKeys,
			"jsValidationMessage",
		);

		input.dispatchEvent(new Event("invalid", { bubbles: true }));

		expect(input.validationMessage.length).toBeGreaterThan(0); // Should use browser default message
	});

	it("does nothing when input is valid", () => {
		document.body.innerHTML = `<form>
            <input type="text" name="test" required value="hello" data-js-validation-message-value-missing="Should not trigger" />
        </form>`;

		const input = document.querySelector("input")!;
		new CustomInvalidValidationMessageItem(
			input,
			["valueMissing"],
			"jsValidationMessage",
		);

		input.dispatchEvent(new Event("invalid", { bubbles: true }));

		expect(input.validationMessage).toBe(""); // No validation message, because it's valid
	});

	it("allows for custom validation message using: %s", async () => {
		const user = userEvent.setup();
		document.body.innerHTML = `<form>
            <input type="email" name="test" required 
                data-js-validation-message-type-mismatch="Invalid email"
                data-js-validation-message-value-missing="Required error" 
                data-js-validation-message-pattern-mismatch="Pattern error" pattern="^i"/>
        </form>`;

		const input = document.querySelector("input")!;
		const instance = new CustomInvalidValidationMessageItem(
			input,
			["valueMissing", "typeMismatch", "patternMismatch"],
			"jsValidationMessage",
		);

		input.value = "";
		input.dispatchEvent(new Event("invalid", { bubbles: true }));
		expect(input.validationMessage).toBe("Required error");

		input.value = "invalid-email";
		input.dispatchEvent(new Event("invalid", { bubbles: true }));
		expect(input.validationMessage).toBe("Invalid email");

		// Trigger a faulty pattern, but since email crashed first and is still wrong it wont be triggered.
		input.value = "not-valid-pattern-and-faulty-email";
		input.dispatchEvent(new Event("invalid", { bubbles: true }));
		expect(input.validationMessage).toBe("Invalid email");
	});
});

// patternMismatch | Value doesn’t match the pattern attribute.
// typeMismatch | Value does not match the type (e.g., type="email" but not a valid email).
// customError | A custom error was set using setCustomValidity().
// valid | All constraints are satisfied.

// tooLong | Value exceeds maxlength.
// tooShort | Value is shorter than minlength.
// badInput | Typically for number inputs where the value can’t be parsed.
// stepMismatch | Number doesn’t match step increment.
