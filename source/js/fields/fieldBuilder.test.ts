import { describe, expect, it, beforeEach } from "@jest/globals";
import FieldBuilder from "./fieldBuilder";
import NullField from "./field/nullField/nullField";
import Checkbox from "./field/checkbox/checkbox";

// Mocks for required constructor arguments
const mockFieldsInitiator = {
	build: () => [],
	getFields: () => [],
	getField: () => null,
};
const mockNotices = {};
const mockFormData = { placeSearchApiUrl: "" };
const mockFormLang = {};

describe("Field Builder", () => {
	let fieldBuilder: FieldBuilder;
	let div: HTMLElement;

	beforeAll(() => {
		jest.spyOn(console, "error").mockImplementation(() => {});
	});

	beforeEach(() => {
		fieldBuilder = new FieldBuilder(
			mockFieldsInitiator as any,
			mockNotices as any,
			mockFormData as any,
			mockFormLang as any,
		);
		div = document.createElement("div");
		div.innerHTML = "";
		div.removeAttribute("data-js-field-name");
		div.removeAttribute("data-js-conditional-logic");
	});

	it("build() returns NullField if fieldElement is missing valid attributes", () => {
		// No attributes
		let missingAttributes = fieldBuilder.build(div, "checkbox", "step1");
		expect(missingAttributes).toBeInstanceOf(NullField);

		// Missing field name
		setAttributes(
			div,
			null,
			'[{"field":"testField","operator":"==","value":1}]',
		);
		let missingFieldName = fieldBuilder.build(div, "checkbox", "step1");
		expect(missingFieldName).toBeInstanceOf(NullField);

		// Missing condition attribute
		setAttributes(div, "testField", null);
		let missingConditionAttribute = fieldBuilder.build(
			div,
			"checkbox",
			"step1",
		);
		expect(missingConditionAttribute).toBeInstanceOf(NullField);
	});

	it("build() returns NullField if type is not found", () => {
		setAttributes(
			div,
			"testField",
			'[{"field":"testField","operator":"==","value":1}]',
		);
		let missingFieldType = fieldBuilder.build(div, "unknownField", "step1");
		expect(missingFieldType).toBeInstanceOf(NullField);
	});

	it("build() returns Checkbox if valid data", () => {
		setAttributes(
			div,
			"testField",
			'[{"field":"testField","operator":"==","value":1}]',
		);
		div.innerHTML = `<input type="checkbox" name="testField" value="1">`;
		let result = fieldBuilder.build(div, "checkbox", "step1");
		expect(result).toBeInstanceOf(Checkbox);
	});

	it("build() returns NullField if no input elements found in the container (when trying to build a checkbox)", () => {
		setAttributes(
			div,
			"testField",
			'[{"field":"testField","operator":"==","value":1}]',
		);
		div.innerHTML = `test`;
		let result = fieldBuilder.build(div, "checkbox", "step1");
		expect(result).toBeInstanceOf(NullField);
	});

	it("build() returns Checkbox even if faulty conditions", () => {
		setAttributes(div, "testField", "0/0/_<>");
		div.innerHTML = `<input type="checkbox" name="testField" value="1">`;
		let result = fieldBuilder.build(div, "checkbox", "step1");
		expect(result).toBeInstanceOf(Checkbox);
	});
});

function setAttributes(
	div: HTMLElement,
	name: any = null,
	condition: any = null,
) {
	if (name) {
		div.setAttribute("data-js-field-name", name);
	} else {
		div.removeAttribute("data-js-field-name");
	}

	if (condition) {
		div.setAttribute("data-js-conditional-logic", condition);
	} else {
		div.removeAttribute("data-js-conditional-logic");
	}

	return div;
}
