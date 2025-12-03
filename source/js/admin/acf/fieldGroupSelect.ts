class FieldGroupSelect implements FieldGroupSelectInterface {
	private group!: FieldStorage;
	private postTypeSelect!: PostTypeSelectInterface;
	private select!: HTMLSelectElement;
	private selectedCache: { [key: string]: string[] } = {};
	private postTypeKeyToCache: string | null = null;
	private constructor(
		private store: StoreInterface,
		private modularityFrontendFormAdminData: ModularityFrontendFormAdminData,
		private field: HTMLElement,
		private groupId: string,
	) {
		this.group = this.store.get(this.groupId) as FieldStorage;
		this.select = this.field.querySelector('select') as HTMLSelectElement;

		if (this.group?.postTypeSelect || !this.select) {
			this.postTypeSelect = this.group.postTypeSelect as PostTypeSelectInterface;
			this.init();
		} else {
			console.error(`PostTypeSelect not found for group ID: ${this.groupId}`);
		}
	}

	/**
	 * Initializes the FieldGroupSelect instance.
	 */
	private init() {
		if (!this.postTypeSelect) {
			return;
		}

		this.postTypeKeyToCache = this.postTypeSelect.getSelected();
		this.updateOptions();
	}

	/**
	 * Updates the options in the select element based on the selected post type.
	 */
	public updateOptions(): void {
		if (!this.postTypeSelect) {
			return;
		}

		this.setSelectedCache();
		this.updateMarkup(this.postTypeSelect.getSelected());
		this.postTypeKeyToCache = this.postTypeSelect.getSelected();
	}

	/**
	 * Caches the currently selected options for the current post type.
	 */
	private setSelectedCache(): void {
		const selectedOptions = this.select.selectedOptions;

		if (!this.postTypeKeyToCache) {
			return;
		}

		const selectedValues: string[] = [];
		[...selectedOptions].forEach((element) => {
			selectedValues.push(element.value);
		});

		this.selectedCache[this.postTypeKeyToCache!] = selectedValues;
	}

	/**
	 * Updates the select markup based on the selected post type.
	 *
	 * @param selectedPostType
	 * @returns
	 */
	private updateMarkup(selectedPostType: string | null): void {
		this.select.length = 0;

		const modularityFrontendFormAcfGroup = selectedPostType
			? this.modularityFrontendFormAdminData.modularityFrontendFormAcfGroups[selectedPostType]
			: null;

		
		if (!modularityFrontendFormAcfGroup || Object.keys(modularityFrontendFormAcfGroup).length === 0) {
			return;
		}

		for (const groupKey in modularityFrontendFormAcfGroup) {
			const option = document.createElement('option');
			option.value = groupKey;
			option.text = modularityFrontendFormAcfGroup[groupKey];

			if (this.selectedCache[selectedPostType!]?.includes(groupKey)) {
				option.selected = true;
			}

			this.select.add(option);
		}
	}

	public static createInstance(
		store: StoreInterface,
		modularityFrontendFormAdminData: ModularityFrontendFormAdminData,
		field: HTMLElement,
		groupId: string,
	): FieldGroupSelect {
		return new FieldGroupSelect(store, modularityFrontendFormAdminData, field, groupId);
	}
}

export default FieldGroupSelect;
