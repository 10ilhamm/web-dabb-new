import './bootstrap';

import Alpine from 'alpinejs';
import html2canvas from 'html2canvas';
import { PageFlip } from 'page-flip';

window.Alpine = Alpine;
window.html2canvas = html2canvas;
window.PageFlip = PageFlip;

Alpine.start();
