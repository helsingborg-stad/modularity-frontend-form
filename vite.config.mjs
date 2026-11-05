import { createViteConfig } from "vite-config-factory";

const entries = {
	"js/init": "./source/js/init.ts",
	"css/main": "./source/sass/main.scss",
	"js/admin": "./source/js/admin/admin.ts",
};

const createConfig = createViteConfig(entries, {
	outDir: "dist",
	manifestFile: "manifest.json",
});

export default (environment) => {
	const config = createConfig(environment);

	return {
		...config,
		build: { ...config.build, cssTarget: "esnext" },
	};
};
