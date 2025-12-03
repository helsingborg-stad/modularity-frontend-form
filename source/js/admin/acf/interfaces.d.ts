interface AcfSelectsInterface {
	init(): void;
}

interface AcfSelect {
	updateOptions(options: any[]): void;
}

interface StoreInterface {
	set(id: string, fieldStorage: FieldStorage): FieldStorage;
	get(id: string): FieldStorage | null;
	addFieldToGroup(id: string, field: FieldGroupSelectInterface): void;
	setPostTypeSelect(id: string, postTypeSelect: PostTypeSelectInterface): void;
}

interface FieldGroupSelectInterface {
	updateOptions(): void;
}

interface PostTypeSelectInterface {
	getSelected(): string | null;
}

interface FetchGroupsInterface {
	fetch(postType: string | null): Promise<any>;
}
