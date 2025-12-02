class FieldGroupSelect implements FieldGroupSelectInterface {
	private group!: FieldStorage;
	private postTypeSelect!: PostTypeSelectInterface;
	private constructor(
		private store: StoreInterface,
		private fetchGroups: FetchGroupsInterface,
		private field: HTMLElement,
		private groupId: string,
	) {
		this.group = this.store.get(this.groupId) as FieldStorage;
		if (this.group?.postTypeSelect) {
			this.postTypeSelect = this.group.postTypeSelect;
			this.init();
		} else {
			console.error(`PostTypeSelect not found for group ID: ${this.groupId}`);
		}
	}

	private init() {
		this.updateOptions();
	}

	public updateOptions(): void {
		this.fetchGroups
			.fetch(this.postTypeSelect.getSelected())
			.then((groups: any) => {
				console.log("Fetched groups:", groups);
			});
	}

	public static createInstance(
		store: StoreInterface,
		fetchGroups: FetchGroupsInterface,
		field: HTMLElement,
		groupId: string,
	): FieldGroupSelect {
		return new FieldGroupSelect(store, fetchGroups, field, groupId);
	}
}

export default FieldGroupSelect;
