import FieldGroupSetup from './acf/fieldGroupSetup';
import { initializeFormBuilder } from './acf/formBuilder';
import Store from './acf/store';

declare const acf: any;
declare const modularityFrontendFormAdminData: ModularityFrontendFormAdminData;

class Admin {
	constructor() {
		this.initAcfSelects();
	}

	/**
	 * Initializes ACF selects.
	 */
	private initAcfSelects(): void {
		if (typeof acf !== 'undefined') {
			new FieldGroupSetup(Store.initStore(), modularityFrontendFormAdminData).init();
			initializeFormBuilder();
		}
	}
}

new Admin();
