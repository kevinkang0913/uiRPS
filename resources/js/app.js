import './bootstrap';

import Alpine from 'alpinejs';

import * as XLSX from "xlsx";
import "luckysheet/dist/plugins/css/pluginsCss.css";
import "luckysheet/dist/plugins/plugins.css";
import "luckysheet/dist/css/luckysheet.css";
import "luckysheet/dist/assets/iconfont/iconfont.css";
import luckysheet from "luckysheet";

window.XLSX = XLSX;
window.luckysheet = luckysheet;



window.Alpine = Alpine;

Alpine.start();
