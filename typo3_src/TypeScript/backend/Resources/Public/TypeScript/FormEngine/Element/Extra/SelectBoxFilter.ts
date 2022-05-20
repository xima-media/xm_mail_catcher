// @ts-nocheck
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

import RegularEvent = require('TYPO3/CMS/Core/Event/RegularEvent');

enum Selectors {
  fieldContainerSelector = '.t3js-formengine-field-group',
  filterTextFieldSelector = '.t3js-formengine-multiselect-filter-textfield',
  filterSelectFieldSelector = '.t3js-formengine-multiselect-filter-dropdown',
}

/**
 * Select field filter functions, see TCA option "multiSelectFilterItems"
 */
class SelectBoxFilter {
  private selectElement: HTMLSelectElement = null;
  private filterText: string = '';
  private availableOptions: NodeListOf<HTMLOptionElement> = null;

  constructor(selectElement: HTMLSelectElement) {
    this.selectElement = selectElement;

    this.initializeEvents();
  }

  private initializeEvents(): void {
    const wizardsElement = this.selectElement.closest('.form-wizards-element');
    if (wizardsElement === null) {
      return;
    }

    new RegularEvent('input', (e: Event): void => {
      this.filter((<HTMLInputElement>e.target).value);
    }).delegateTo(wizardsElement, Selectors.filterTextFieldSelector);

    new RegularEvent('change', (e: Event): void => {
      this.filter((<HTMLInputElement>e.target).value);
    }).delegateTo(wizardsElement, Selectors.filterSelectFieldSelector);
  }

  /**
   * Filter the actual items
   *
   * @param {string} filterText
   */
  private filter(filterText: string): void {
    this.filterText = filterText;
    if (this.availableOptions === null) {
      this.availableOptions = this.selectElement.querySelectorAll('option');
    }

    const matchFilter = new RegExp(filterText, 'i');
    this.availableOptions.forEach((option: HTMLOptionElement): void => {
      option.hidden = filterText.length > 0 && option.textContent.match(matchFilter) === null;
    });
  }
}

export = SelectBoxFilter;
