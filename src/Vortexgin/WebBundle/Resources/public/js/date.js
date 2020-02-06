/**
 * Created by dailysocial on 18/04/17.
 * Data prototype collection
 * Author Tommy Dian P
 */

var CustomDate = {
    _listMonth: [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ],
    _listShortMonth: [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des',
    ],
    getMonth(month) {
        var month = month || 0;
        return CustomDate._listMonth[month];
    },
    format(dates, format) {
        var Y = dates.getFullYear(),
            m = ((dates.getMonth() + 1 < 10) ? '0' : '') + (dates.getMonth() + 1),
            n = (dates.getMonth() + 1),
            F = this._listMonth[dates.getMonth()],
            M = this._listShortMonth[dates.getMonth()],
            d = ((dates.getDate() < 10) ? '0' : '') + dates.getDate(),
            j = dates.getDate(),
            H = ((dates.getHours() < 10) ? '0' : '') + dates.getHours(),
            h = dates.getHours(),
            i = ((dates.getMinutes() < 10) ? '0' : '') + dates.getMinutes(),
            s = ((dates.getSeconds() < 10) ? '0' : '') + dates.getSeconds();

        format = format.replace(/\%Y/g, Y);
        format = format.replace(/\%m/g, m);
        format = format.replace(/\%n/g, n);
        format = format.replace(/\%F/g, F);
        format = format.replace(/\%M/g, M);
        format = format.replace(/\%d/g, d);
        format = format.replace(/\%j/g, j);
        format = format.replace(/\%H/g, H);
        format = format.replace(/\%h/g, h);
        format = format.replace(/\%i/g, i);
        format = format.replace(/\%s/g, s);

        return format;
    }
};
Date.prototype.customFormat = function(format) {
    var format = format || 'Y-m-d',
        time = this.getFullYear() +
        '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
        '-' + ((this.getDate() < 10) ? '0' : '') + this.getDate();
    if (format == 'd-m-Y') {
        time = ((this.getDate() < 10) ? '0' : '') + this.getDate() +
            '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-' + this.getFullYear();
    } else if (format == 'Y-m-d G:i:s') {
        time = this.getFullYear() + '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-' + ((this.getDate() < 10) ? '0' : '') + this.getDate() +
            ' ' + ((this.getHours() < 10) ? '0' : '') + this.getHours() +
            ':' + ((this.getMinutes() < 10) ? '0' : '') + this.getMinutes() +
            ':' + ((this.getSeconds() < 10) ? '0' : '') + this.getSeconds();
    } else if (format == 'dayofmonth') {
        time = this.getFullYear() +
            '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-01';
    }

    return time;
}

sec2time = function(timeInSeconds) {
    var pad = function(num, size) { return ('000' + num).slice(size * -1); },
        time = parseFloat(timeInSeconds).toFixed(3),
        hours = Math.floor(time / 60 / 60),
        minutes = Math.floor(time / 60) % 60,
        seconds = Math.floor(time - minutes * 60),
        milliseconds = time.slice(-3);
    return pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2);
}