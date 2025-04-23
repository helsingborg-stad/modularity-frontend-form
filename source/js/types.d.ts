type ModularityFrontendForm = {
    placeSearchApiUrl: string;
    lang: ModularityFrontendFormLang;
}

type ModularityFrontendFormLang = {
    noResultsFound?: string;
    searchPlaceholder?: string;
    next?: string;
    previous?: string;
    submit?: string;
};