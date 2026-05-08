interface FormParams {
	moduleId: number;
	postId: number;
	token: Token32;
}

type FetchedFormData = {
	[fieldName: string]: any;
};

interface FormPopulatorInterface extends FormActionInterface {
	tryPopulateForm(): Promise<void>;
}

type FakeFile = {
	name: string;
	type: string;
	id: string;
	url: string;
	size: number;
};

type Token32 = string & { __lengthBrand: 32 };
