export function usePagination() {
    const translatePaginationLabel = (label: string) => {
        return label
            .replace(/&laquo;\s*Previous/i, '&laquo; 上一页')
            .replace(/Next\s*&raquo;/i, '下一页 &raquo;');
    };

    return {
        translatePaginationLabel
    };
}
