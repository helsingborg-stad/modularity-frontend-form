import { describe, expect, it } from "@jest/globals";
import NullCondition from "./nullCondition";

describe("Null Condition", () => {
	const nullCondition = new NullCondition();

	it("getConditionFieldNames() returns an empty array", () => {
		expect(nullCondition.getConditionFieldNames()).toEqual([]);
	});

	it("getConditions() returns an empty array", () => {
		expect(nullCondition.getConditions()).toEqual([]);
	});

	it("validate() returns true", () => {
		expect(nullCondition.validate()).toEqual(true);
	});
});
