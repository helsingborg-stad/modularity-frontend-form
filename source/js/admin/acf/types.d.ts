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
	postTypeSelect: PostTypeSelectInterface;
};

type FieldsStorage = {
	[id: string]: FieldStorage;
};
