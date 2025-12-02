import FieldGroupSetup from "./acf/fieldGroupSetup";
import Store from "./acf/store";

declare const acf: any;

class Admin {
	constructor() {
		this.initAcfSelects();
	}

	private initAcfSelects(): void {
		if (typeof acf !== "undefined") {
			new FieldGroupSetup(Store.initStore()).init();
		}
	}
}

new Admin();
