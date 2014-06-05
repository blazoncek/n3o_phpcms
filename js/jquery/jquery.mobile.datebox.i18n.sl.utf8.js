/*
 * jQuery Mobile Framework : plugin to provide a date and time picker.
 * Copyright (c) JTSage
 * CC 3.0 Attribution.  May be relicensed without permission/notifcation.
 * https://github.com/jtsage/jquery-mobile-datebox
 *
 * Translation by: Blaž Kristan <blaz@kristan-sp.si>
 *
 */

jQuery.extend(jQuery.mobile.datebox.prototype.options.lang, {
	'sl': {
		setDateButtonLabel: "Nastavi datum",
		setTimeButtonLabel: "Nastavi čas",
		setDurationButtonLabel: "Nastavi trajanje",
		calTodayButtonLabel: "Danes",
		titleDateDialogLabel: "Izberi datum",
		titleTimeDialogLabel: "Izberi čas",
		daysOfWeek: ["nedelja", "ponedeljek", "torek", "sreda", "četrtek", "petek", "sobota"],
		daysOfWeekShort: ["ne", "po", "to", "sr", "če", "pe", "so"],
		monthsOfYear: ["januar", "februar", "marec", "april", "maj", "junij", "julij", "avgust", "september", "oktober", "november", "december"],
		monthsOfYearShort: ["jan", "feb", "mar", "apr", "maj", "jun", "jul", "avg", "sep", "okt", "nov", "dec"],
		durationLabel: ["dni", "ur", "minut", "sekund"],
		durationDays: ["dan", "dni"],
		tooltip: "Izberi datum",
		nextMonth: "Naslednji mesec",
		prevMonth: "Predhodni mesec",
		timeFormat: 24,
		headerFormat: '%A, %-d. %B %Y',
		dateFieldOrder: ['d', 'm', 'y'],
		timeFieldOrder: ['h', 'i', 'a'],
		slideFieldOrder: ['y', 'm', 'd'],
		dateFormat: "%-d.%-m.%Y",
		useArabicIndic: false,
		isRTL: false,
		calStartDay: 1,
		clearButton: "Počisti",
		durationOrder: ['d', 'h', 'i', 's'],
		meridiem: ["AM", "PM"],
		timeOutput: "%H:%M",
		durationFormat: "%Dd %DA, %Dl:%DM:%DS",
		calDateListLabel: "Drugi datumi"
	}
});
jQuery.extend(jQuery.mobile.datebox.prototype.options, {
	useLang: 'sl'
});

