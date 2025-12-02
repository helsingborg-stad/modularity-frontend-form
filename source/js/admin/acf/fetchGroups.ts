declare const wpApiSettings: {
	nonce: string;
	root: string;
	versionString: string;
};

class FetchGroups implements FetchGroupsInterface {
	private ENDPOINT = "modularity-frontend-form/v1/acf-field-groups";
	private POST_TYPE_PARAM = "post_type";
	private static instance: FetchGroups;

	private cache: Map<string, Promise<FieldGroupResponse>> = new Map();

	private constructor() {}

	public fetch(postType: string | null): Promise<FieldGroupResponse> {
		if (!postType) return Promise.resolve({} as FieldGroupResponse);

		if (this.cache.has(postType)) {
			return this.cache.get(postType) as Promise<FieldGroupResponse>;
		}

		const request = fetch(
			`${wpApiSettings.root}${this.ENDPOINT}?${this.POST_TYPE_PARAM}=${postType}`,
			{
				method: "GET",
			},
		).then((res) => res.json());

		this.cache.set(postType, request);
		return request;
	}

	public static createInstance(): FetchGroups {
		if (!FetchGroups.instance) {
			FetchGroups.instance = new FetchGroups();
		}
		return FetchGroups.instance;
	}
}

export default FetchGroups;
