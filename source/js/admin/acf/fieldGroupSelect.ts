class FieldGroupSelect implements FieldGroupSelectInterface {
	private group!: FieldStorage;
	private postTypeSelect!: PostTypeSelectInterface;
	private select!: HTMLSelectElement;
	private selectedCache: { [key: string]: string[] } = {};
	private postTypeKeyToCache: string | null = null;
	private wordpressDefaultFields: { [key: string]: string } = {};

	private constructor(
		private store: StoreInterface,
		private modularityFrontendFormAdminData: ModularityFrontendFormAdminData,
		private field: HTMLElement,
		private groupId: string,
	) {
		this.group = this.store.get(this.groupId) as FieldStorage;
		this.select = this.field.querySelector('select') as HTMLSelectElement;
		this.wordpressDefaultFields =
			this.modularityFrontendFormAdminData.modularityFrontendFormAcfGroups[
				this.modularityFrontendFormAdminData.modularityFrontendFormWordpressFieldsKey
			] || {};

		console.log(this.modularityFrontendFormAdminData.modularityFrontendFormAcfGroups);

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
	 * The sorting part is necessary due to the element not being structured after page reload.
	 */
	private setSelectedCache(): void {
		if (!this.postTypeKeyToCache) return;

		const result = [...this.select.selectedOptions]
			.map((opt) => ({
				index: opt.dataset.i ? Number(opt.dataset.i) : null,
				value: opt.value,
			}))
			.sort((a, b) => {
				if (a.index === null && b.index === null) return 0;
				if (a.index === null) return 1;
				if (b.index === null) return -1;
				return a.index - b.index;
			})
			.map((x) => x.value);

		this.selectedCache[this.postTypeKeyToCache] = result;
	}

	/**
	 * Updates the select markup based on the selected post type.
	 *
	 * @param selectedPostType
	 * @returns
	 */
	private updateMarkup(selectedPostType: string | null): void {
		this.select.length = 0;

		let groups = selectedPostType
			? this.modularityFrontendFormAdminData.modularityFrontendFormAcfGroups[selectedPostType]
			: {};

		groups = { ...groups, ...this.wordpressDefaultFields };

		if (!groups) {
			return;
		}

		const keys = Object.keys(groups);
		const selected = this.selectedCache[selectedPostType!] ?? [];
		const notSelected = keys.filter((key) => !selected.includes(key));

		const orderedKeyss = [...notSelected, ...selected];

		for (const groupKey of orderedKeyss) {
			const group = groups[groupKey];
			if (!group) continue;

			const option = this.createOption(groupKey, group, selected.includes(groupKey) || false);

			this.select.add(option);
		}
	}

	private createOption(value: string, text: string, selected: boolean): HTMLOptionElement {
		const option = document.createElement('option');
		option.value = value;
		option.text = text;
		option.selected = selected;
		return option;
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
