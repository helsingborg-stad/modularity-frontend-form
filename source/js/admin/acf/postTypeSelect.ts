class PostTypeSelect implements PostTypeSelectInterface {
	private constructor(
		private store: StoreInterface,
		private field: HTMLElement,
	) {
		console.log("PostTypeSelect initialized: " + this.field);
	}

	private init(): this {
		return this;
	}

	public getSelected(): string | null {
		return null;
	}

	public static createInstance(
		store: StoreInterface,
		field: HTMLElement,
	): PostTypeSelect {
		return new PostTypeSelect(store, field).init();
	}
}

export default PostTypeSelect;
