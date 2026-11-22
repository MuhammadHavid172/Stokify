import './bootstrap';

import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';
import { initInstantPrefetch } from './prefetch';

window.Alpine = Alpine;
window.Turbo = Turbo;

Alpine.start();
initInstantPrefetch();
