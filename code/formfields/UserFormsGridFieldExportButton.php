<?php
/**
 * Custom gridfield export button for user forms. This export button
 * differs from the standard one by not trying to access values via the
 * title field when the original value is false. The standard button would
 * cause an error if the title contained a '.' character, which is a common
 * pattern in user-defined form field labels. The error would arise
 * because DataObject::relField() would try to access a non-existent
 * database column.
 *
 * See https://github.com/silverstripe/silverstripe-userforms/issues/271
 */
class UserFormsGridFieldExportButton extends GridFieldExportButton
{
    /**
     * Generate export fields for CSV.
     *
     * @param GridField $gridField
     *
     * @return array
     */
    public function generateExportFileData($gridField)
    {
        $separator = $this->csvSeparator;
        $csvColumns = ($this->exportColumns)
            ? $this->exportColumns
            : singleton($gridField->getModelClass())->summaryFields();
        $fileData = '';
        $columnData = array();
        $fieldItems = new ArrayList();
        if ($this->csvHasHeader) {
            $headers = array();
            // determine the CSV headers. If a field is callable (e.g. anonymous function) then use the
            // source name as the header instead
            foreach ($csvColumns as $columnSource => $columnHeader) {
                $headers[] = (!is_string($columnHeader) && is_callable($columnHeader)) ? $columnSource : $columnHeader;
            }
            $fileData .= '"'.implode("\"{$separator}\"", array_values($headers)).'"';
            $fileData .= "\n";
        }

        //Remove GridFieldPaginator as we're going to export the entire list.
        $gridField->getConfig()->removeComponentsByType('GridFieldPaginator');

        $items = $gridField->getManipulatedList();
        // @todo should GridFieldComponents change behaviour based on whether others are available in the config?
        foreach ($gridField->getConfig()->getComponents() as $component) {
            if ($component instanceof GridFieldFilterHeader || $component instanceof GridFieldSortableHeader) {
                $items = $component->getManipulatedData($gridField, $items);
            }
        }
        foreach ($items->limit(null) as $item) {
            if (!$item->hasMethod('canView') || $item->canView()) {
                $columnData = array();
                foreach ($csvColumns as $columnSource => $columnHeader) {
                    if (!is_string($columnHeader) && is_callable($columnHeader)) {
                        if ($item->hasMethod($columnSource)) {
                            $relObj = $item->{$columnSource}();
                        } else {
                            $relObj = $item->relObject($columnSource);
                        }
                        $value = $columnHeader($relObj);
                    } else {
                        $value = $gridField->getDataFieldValue($item, $columnSource);
                    }
                    $value = str_replace(array("\r", "\n"), "\n", $value);
                    $columnData[] = '"'.str_replace('"', '""', $value).'"';
                }
                $fileData .= implode($separator, $columnData);
                $fileData .= "\n";
            }
            if ($item->hasMethod('destroy')) {
                $item->destroy();
            }
        }

        return $fileData;
    }
}
