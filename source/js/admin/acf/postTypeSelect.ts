class PostTypeSelect implements PostTypeSelectInterface {
	private select: HTMLSelectElement;
	private constructor(
		private store: StoreInterface,
		private field: HTMLElement,
		private groupId: string,
	) {
		this.select = this.field.querySelector('select') as HTMLSelectElement;

		if (!this.select) {
			console.error(`Select element not found in field for group ID: ${this.groupId}`);
		} else {
			this.init();
		}
	}

	/**
	 * Initializes the PostTypeSelect instance.
	 */
	private init(): void {
		this.select.addEventListener('change', (e: Event) => {
			this.store.get(this.groupId)?.fields.forEach((fieldGroupSelect: FieldGroupSelectInterface) => {
				fieldGroupSelect.updateOptions();
			});
		});
	}

	/**
	 * Gets the currently selected post type.
	 * @returns string | null
	 */
	public getSelected(): string | null {
		return this.select.value || null;
	}

	public static createInstance(store: StoreInterface, field: HTMLElement, groupId: string): PostTypeSelect {
		return new PostTypeSelect(store, field, groupId);
	}
}

export default PostTypeSelect;
