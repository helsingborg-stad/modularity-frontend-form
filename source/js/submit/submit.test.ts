import Submit from "./submit";
import ServerErrors from "./serverErrors";
import Form from "../form/form";
import FormMode from "../form/formModeEnum";
import FieldBuilder from "../fields/fieldBuilder";
import ConditionBuilder from "../conditions/conditionBuilder";
import Notice from "../fields/notice/notice";
import StepValidator from "../fields/validation/stepValidator";
import StepsFactory from "../steps/stepsFactory";
import StepNavigator from "../steps/stepNavigator";
import Steps from "../steps/steps";
import { SubmitStatus } from "../formStatus/enum";

const originalFetch = Object.getOwnPropertyDescriptor(global, "fetch");
const originalScrollIntoView = Object.getOwnPropertyDescriptor(HTMLElement.prototype, "scrollIntoView");
const originalUrl = window.location.href;
let windowListeners: jest.SpyInstance;

const names = ["mod-frontend-form[field_phone]", "mod-frontend-form[field_other]",
	"mod-frontend-form[field_rows][0][field_phone]", "mod-frontend-form[field_rows][1][field_phone]"];

function setup(mode = FormMode.Post) {
	window.history.replaceState({}, "", "/?postId=42&token=test");
	// Only our JS hooks are fixtures; component-library markup and styling are not reproduced.
	document.body.innerHTML = `<div data-js-frontend-form>
		<template data-js-frontend-form-error-notice><span data-js-frontend-form-notice-message></span></template>
		<template data-js-frontend-form-success-notice></template>
		<form data-js-frontend-form-id="123">
			<span id="hint">Help</span>
			<section data-js-frontend-form-step-container><div data-js-frontend-form-step="0">
				${names.map((name, index) => `<div data-js-field="tel" data-js-field-name="${name}" data-js-conditional-logic="0"><input type="tel" name="${name}" value="value-${index}" aria-describedby="hint"></div>`).join("")}
			</div></section>
			<section data-js-frontend-form-step-container><div data-js-frontend-form-step="1"></div></section>
		</form>
	</div>`;
	const container = document.querySelector<HTMLElement>("[data-js-frontend-form]")!;
	const formElement = container.querySelector("form")!;
	const builder = new FieldBuilder({} as any, new Notice(container), {} as any, {} as any);
	const conditions = new ConditionBuilder(builder);
	container.querySelectorAll<HTMLElement>("[data-js-field]").forEach(element => builder.build(element, element.dataset.jsField!, "0").init(conditions));
	const validator = new StepValidator();
	validator.init(builder);
	const steps = StepsFactory.create(container, validator);
	const ui = { handleValidity: jest.fn(), updateButtonStates: jest.fn(), showAndHideSteps: jest.fn() };
	const status = { setStatus: jest.fn() };
	const renderer = { reset: jest.fn() };
	const submit = new Submit(new Form(formElement, mode), { apiRoutes: {
		submitForm: "https://example.test/create", updateForm: "https://example.test/update",
	} } as any, {} as any, { get: async () => "nonce" } as any,
		status as any, renderer as any, new ServerErrors(builder, formElement));
	const navigator = new StepNavigator(steps, validator, submit, ui as any, container, 1);
	new Steps(steps, navigator, ui as any, document.createElement("button"), document.createElement("button"), 123).init();
	return { submit, inputs: Array.from(formElement.querySelectorAll<HTMLInputElement>("input")), navigator, ui, steps,
		status: () => {
			const [state, message, , , , returnAction, , hideOverlay = false] = status.setStatus.mock.lastCall!;
			return { state, message, returnAction, hideOverlay };
		} };
}

function respond(error: any, ok = false) {
	(global.fetch as jest.Mock).mockResolvedValueOnce({ ok, json: async () => error });
}

beforeEach(() => {
	windowListeners = jest.spyOn(window, "addEventListener");
	global.fetch = jest.fn();
	HTMLElement.prototype.scrollIntoView = jest.fn();
});
afterEach(() => {
	for (const [type, listener, options] of windowListeners.mock.calls) window.removeEventListener(type, listener, options);
	windowListeners.mockRestore();
	if (originalFetch) Object.defineProperty(global, "fetch", originalFetch);
	else Reflect.deleteProperty(global, "fetch");
	if (originalScrollIntoView) Object.defineProperty(HTMLElement.prototype, "scrollIntoView", originalScrollIntoView);
	else Reflect.deleteProperty(HTMLElement.prototype, "scrollIntoView");
	window.history.replaceState(null, "", originalUrl);
	document.body.replaceChildren();
});

it.each([FormMode.Post, FormMode.Update])("shows field errors and requests the first affected step; corrects and resubmits (%s)", async mode => {
	const view = setup(mode);
	respond({ code: "validation_error", message: "<b>Too short</b>", data: { status: 400, field_errors: [
		{ input: names[3], message: "Invalid country code" },
		{ input: names[1], message: "Unsupported characters" },
		{ input: names[0], message: "<b>Too short</b>" },
	] } });
	await view.submit.submit();
	expect(view.navigator.getActiveStepIndex()).toBe(1);
	expect(view.status().hideOverlay).toBe(false);
	expect(view.status().returnAction.showFields).toBe(true);
	view.status().returnAction.return();
	expect(view.navigator.getActiveStepIndex()).toBe(0);
	expect(view.ui.showAndHideSteps).toHaveBeenCalledWith(view.steps[0], view.steps[1]);
	expect(view.inputs.map(input => input.value)).toEqual(["value-0", "value-1", "value-2", "value-3"]);
	for (const [index, message] of [[0, "<b>Too short</b>"], [1, "Unsupported characters"], [3, "Invalid country code"]] as const) {
		const input = view.inputs[index];
		expect(input.parentElement!.textContent).toContain(message);
		expect(input.parentElement!.querySelector("b")).toBeNull();
		expect(input.getAttribute("aria-invalid")).toBe("true");
		const id = input.getAttribute("aria-describedby")!.split(" ")[1];
		expect(document.getElementById(id)?.textContent).toBe(message);
		input.value = `corrected-${index}`;
		input.dispatchEvent(new Event("input", { bubbles: true }));
		expect(input.parentElement!.querySelector("[role=alert]")).toBeNull();
		expect(input.getAttribute("aria-describedby")).toBe("hint");
	}
	expect(view.inputs[2].parentElement!.querySelector("[role=alert]")).toBeNull();
	respond({ data: { field_errors: [{ input: names[0], message: "A new validation result" }] } });
	await view.submit.submit();
	expect(view.inputs[0].parentElement!.textContent).toContain("A new validation result");
	expect((fetch as jest.Mock).mock.calls[1][1].body.get(names[0])).toBe("corrected-0");
	view.inputs[0].value = "valid";
	view.inputs[0].dispatchEvent(new Event("change", { bubbles: true }));
	respond({}, true);
	await view.submit.submit();
	expect(view.status().state).toBe(SubmitStatus.Success);
});

it("keeps a separate non-field message when it shares a code with field errors", async () => {
	const view = setup();
	respond({ code: "validation_error", message: "Editing is disabled", data: { field_errors: [
		{ input: names[0], message: "Too short" },
	] }, additional_errors: [{ code: "validation_error", message: "Too short", data: { field_errors: [
		{ input: names[0], message: "Too short" },
	] } }] });
	await view.submit.submit();
	expect(view.inputs[0].parentElement!.textContent).toContain("Too short");
	expect(view.status().message).toBe("Editing is disabled");
	expect(view.status().hideOverlay).toBe(false);
});

it.each(["", "missing"])("keeps unmatched errors even when a field uses the same message (%s)", async input => {
	const view = setup();
	respond({ message: "Required", data: { field_errors: [
		{ input: names[0], message: "Required" }, { input, message: "Required" },
	] } });
	await view.submit.submit();
	expect(view.inputs[0].parentElement!.textContent).toContain("Required");
	expect(view.status().message).toBe("Required");
	expect(view.status().hideOverlay).toBe(false);
});

it("preserves additional WordPress errors and forwards unmatched messages to form status", async () => {
	const view = setup();
	respond({ message: "Fields invalid", data: { field_errors: [{ input: names[0], message: "First" }] },
		additional_data: [{ field_errors: [{ input: names[3], message: "Second row" }] }],
		additional_errors: [
			{ message: "Service unavailable" },
			{ data: { field_errors: [{ input: "missing", message: "Unmatched field" }, { input: "", message: "Fieldless" }] } },
		] });
	await view.submit.submit();
	expect(view.inputs[0].parentElement!.textContent).toContain("First");
	expect(view.inputs[3].parentElement!.textContent).toContain("Second row");
	expect(view.status().message).toContain("Service unavailable");
	expect(view.status().message).toContain("Unmatched field");
	expect(view.status().message).toContain("Fieldless");
});

it.each([{}, { message: "" }, { data: { field_errors: [{ input: "missing", message: "" }] } }])("provides a generic fallback for unusable errors %j", async error => {
	const view = setup(); respond(error);
	await view.submit.submit();
	expect(view.status().state).toBe(SubmitStatus.Error);
	expect(view.status().message).toBe("Form submission failed. Please try again.");
	expect(view.status().hideOverlay).toBe(false);
});
