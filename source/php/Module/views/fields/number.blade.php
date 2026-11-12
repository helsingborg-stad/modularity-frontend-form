{{-- view, disabled and rows are internal keys, not field component params --}}
@field(array_diff_key($field, array_flip(['view', 'disabled', 'rows'])))
@endfield