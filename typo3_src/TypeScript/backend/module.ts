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

/**
 * Module: @typo3/backend/module
 */

export interface ModuleState {
  url: string;
  title?: string|null;
  module?: string;
}

/**
 * @internal
 */
export interface Module {
  name: string;
  component: string;
  navigationComponentId: string;
  link: string;
}

/**
 * Gets the module properties from module information data attribute
 *
 * @param {string} name
 * @returns {Module}
 * @internal
 */
export function getRecordFromName(name: string): Module {
  const parsedRecord = getParsedRecordFromName(name);
  if (parsedRecord === null) {
    return {
      name: name,
      component: '',
      navigationComponentId: '',
      link: ''
    };
  }
  return {
    name: name,
    component: parsedRecord.component || '',
    navigationComponentId: parsedRecord.navigationComponentId || '',
    link: parsedRecord.link || '',
  };
}

interface ParsedInformation {
  [index: string]: Partial<Module>;
}

/**
 * Runtime cache of json serialized module information
 */
let parsedInformation: ParsedInformation = null;

function getParsedRecordFromName(name: string): Partial<Module>|null {
  if (parsedInformation === null) {
    const modulesInformation: string = String((document.querySelector('.t3js-scaffold-modulemenu') as HTMLElement)?.dataset.modulesInformation || '');
    if (modulesInformation !== '') {
      try {
        parsedInformation = JSON.parse(modulesInformation);
      } catch (e) {
        console.error('Invalid modules information provided.');
        parsedInformation = null;
      }
    }
  }
  if (parsedInformation !== null && name in parsedInformation) {
    return parsedInformation[name];
  }
  return null;
}
