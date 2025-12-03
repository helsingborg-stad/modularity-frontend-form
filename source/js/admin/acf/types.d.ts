type AcfField = {
	$el: JQuery<HTMLElement>;
	cid: string;
	data: {
		type: string;
		key: string;
		name: string;
	};
};

type FieldStorage = {
	id: string;
	group: HTMLElement;
	fields: FieldGroupSelectInterface[];
	postTypeSelect: PostTypeSelectInterface | null;
};

type FieldsStorage = {
	[id: string]: FieldStorage;
};

type ModularityFrontendFormAcfGroup = {
	[id: string]: string;
};

type ModularityFrontendFormAcfGroups = {
	[postType: string]: ModularityFrontendFormAcfGroup;
};
