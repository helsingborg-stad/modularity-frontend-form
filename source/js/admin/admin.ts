import FieldGroupSetup from './acf/fieldGroupSetup';
import Store from './acf/store';

declare const acf: any;
declare const modularityFrontendFormAcfGroups: ModularityFrontendFormAcfGroups;

class Admin {
	constructor() {
		this.initAcfSelects();
	}

	/**
	 * Initializes ACF selects.
	 */
	private initAcfSelects(): void {
		if (typeof acf !== 'undefined') {
			new FieldGroupSetup(Store.initStore(), modularityFrontendFormAcfGroups).init();
		}
	}
}

new Admin();
