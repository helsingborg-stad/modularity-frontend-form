type ModularityFrontendFormData = {
    placeSearchApiUrl: string;
    apiRoutes?: array;
}

type ModularityFrontendFormLang = {
    noResultsFound?: string;
    searchPlaceholder?: string;
    next?: string;
    previous?: string;
    submit?: string;
    atLeastOneValueIsRequired?: string;
    loading?: string;

    submitInit?: string;
    submitUrlError?: string;
    submitError?: string;
    submitSuccess?: string;

    communicationError?: string;

    nonceRequest?: string;
    nonceUrlMissing?: string;
    nonceRequestFailed?: string;
    nonceRequestSuccess?: string;

    statusTitleLoading?: string;
    statusTitleSucess?: string;
    statusTitleError?: string;
    statusTitleSubmitting?: string;

    bold?: string;
    italic?: string;
    underline?: string;
    strikeThrough?: string;
    ulist?: string;
    olist?: string;
    link?: string;
    blockquote?: string;
    heading?: string;
    subheading?: string;
};