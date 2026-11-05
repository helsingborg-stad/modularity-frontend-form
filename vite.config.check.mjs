import assert from "node:assert/strict";
import test from "node:test";
import createConfig from "./vite.config.mjs";

test("production builds target modern CSS without changing the JavaScript target", () => {
	const config = createConfig({ mode: "production", command: "build" });

	assert.equal(config.build.cssTarget, "esnext");
	assert.equal(config.build.target, undefined);
});

test("development builds use the same CSS target by default", () => {
	const config = createConfig({ mode: "development", command: "build" });

	assert.equal(config.build.cssTarget, "esnext");
});