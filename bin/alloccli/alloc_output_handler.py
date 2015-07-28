# alloc library for outputting ascii or csv tables
import sys
import re
import csv
import subprocess
from prettytable import PrettyTable
from sys import stdout

# Some changes to the PrettyTable API between 0.5 and 0.6, fix it up as required
# https://code.google.com/p/prettytable/issues/detail?id=21
# This can go if/when flora's OS is updated
if 'set_field_names' not in dir(PrettyTable):
    def set_field_names(self, fields):
        # pass lint
        self.field_names = fields

    def set_field_align(self, field, align):
        # pass lint
        self.align[field] = align
    PrettyTable.set_field_names = set_field_names
    PrettyTable.set_field_align = set_field_align


class alloc_output_handler:

    # alloc library for outputting ascii or csv tables

    def __init__(self):
        # Not necessary.
        pass

    def __get_only_these_fields(self, alloc, entity, rows, only_these_fields):
        # Reduce a list by removing certain columns/fields.
        rtn = []
        inverted_field_names = dict(
            [[v, k] for k, v in alloc.field_names[entity].items()])

        # Print all fields
        if 'all' in only_these_fields:
            for k, v in rows.items():
                for name, value in v.items():
                    del(value)  # pylint
                    rtn.append(name)
                    if name in alloc.field_names[entity]:
                        rtn.append(alloc.field_names[entity][name])
                    else:
                        rtn.append(name)
                break
        # Print a selection of fields
        else:
            for name in only_these_fields:
                if name in inverted_field_names:
                    name = inverted_field_names[name]
                rtn.append(name)
                if name in alloc.field_names[entity]:
                    rtn.append(alloc.field_names[entity][name])
                else:
                    rtn.append(name)
        return rtn

    def __get_sorted_rows(self, alloc, entity, rows, sortby):
        # Sort the rows of a list.
        f = ''  # satisfy pylint
        rows = rows.items()
        if not sortby:
            return rows
        inverted_field_names = dict(
            [[v, k] for k, v in alloc.field_names[entity].items()])

        sortby.reverse()

        # load up fields
        k, fields = rows[0]

        # Check that any attempted sortby columns are actually in the table
        for k in sortby:
            # Strip leading underscore (used in reverse sorting eg: _Rate)
            if k and re.sub("^_", "", k) not in fields and re.sub("^_", "", k) not in inverted_field_names:
                alloc.err("Sort column not found: " + k)

        def sort_func(row):
            # Callback function to return the actual value that should be sorted on.
            try:
                val = row[1][f]
            except:
                try:
                    val = row[1][inverted_field_names[f]]
                except:
                    return ''

            # val is the actual value in the field
            try:
                return int(val)
            except:
                try:
                    return float(val)
                except:
                    try:
                        return val.lower()
                    except:
                        return val

        for f in sortby:
            reverse = False
            if f and f[0] == "_":
                reverse = True
                f = f[1:]  # chop leading underscore
            rows = sorted(rows, key=sort_func, reverse=reverse)
        return rows

    def __get_widest_field_lengths(self, rows, field_names):
        # Obtain max lengths of rows of fields.
        # Note that an empty field's column header can still have additional
        # padding, if another value in its column is wider.
        x = 0
        lengths = {}
        for row in rows:
            for v in row:
                v = str(v)
                fn = str(field_names[x])
                if len(v) < len(fn):
                    v = fn
                if fn not in lengths or (fn in lengths and len(v) > int(lengths[fn])):
                    lengths[fn] = len(v)
                x += 1
            x = 0
        return lengths

    def __fit_rows_to_screen(self, alloc, rows, field_names, width):
        # Truncate the final column in a table so that it fits on the screen.

        # We only truncate the rows if we've been configured to
        if 'alloc_trunc' not in alloc.config or not alloc.config['alloc_trunc']:
            return rows

        lengths = self.__get_widest_field_lengths(rows, field_names)
        rows2 = []
        for row in rows:
            s = ''
            sep = ''
            x = 0
            for v in row:
                v = str(v)
                fn = str(field_names[x])
                if len(v) < len(fn):
                    v = fn
                if len(v) < lengths[fn]:
                    v = v.ljust(lengths[fn])
                s += sep + v
                sep = ' | '
                x += 1

            # Simulate a normal row in the table
            s = '| ' + s + ' |'

            # Useful for debugging
            # print s

            # fn will be the final cell in the row
            sum_of_bits = 0
            for k, l in lengths.items():
                if k != fn:
                    sum_of_bits += l + 3

            # If the row is wider than the width of the terminal
            if len(s) > width and sum_of_bits + len(fn) + 3 < width:
                end = len(row) - 1
                row[end] = row[end].ljust(lengths[fn])[:-(len(s) - width)]
            rows2.append(row)
        return rows2

    def print_table(self, alloc, entity, rows, only_these_fields, sort=False, transforms=None):
        # For printing out results in an ascii table or CSV format.
        if alloc.quiet:
            return
        if not rows:
            return

        if not isinstance(sort, list):
            sort = [sort]
        if not isinstance(only_these_fields, list):
            only_these_fields = [only_these_fields]

        only_these_fields = self.__get_only_these_fields(
            alloc, entity, rows, only_these_fields)
        field_names = only_these_fields[1::2]

        # Re-order the table, this changes the dict to a list i.e.
        # dict.items().
        rows = self.__get_sorted_rows(alloc, entity, rows, sort)
        if rows:
            rows2 = []
            for k_, row in rows:
                row = self.__get_row(
                    alloc, entity, row, only_these_fields, transforms)
                rows2.append(row)
            rows = rows2

        if alloc.csv:
            csv_table = csv.writer(sys.stdout, lineterminator="\n")
            for row in rows:
                csv_table.writerow([unicode(s).encode('utf-8') for s in row])

        else:
            table = PrettyTable()
            table.set_field_names(field_names)
            # table.field_names = field_names
            for label in field_names:
                if '$' in label:
                    table.set_field_align(label, "r")
                else:
                    table.set_field_align(label, "l")
                    # table.align[label] = 'l'

            if stdout.isatty():
                proc = subprocess.Popen(
                    ['stty', 'size'], stdout=subprocess.PIPE, stderr=open("/dev/null", "w"))
                ret = proc.wait()
                if ret == 0:
                    height_, width = proc.communicate()[0].split()
                    width = int(width)
                    rows = self.__fit_rows_to_screen(
                        alloc, rows, field_names, width)
            for row in rows:
                table.add_row(row)
            print unicode(table.get_string(header=True)).encode('utf-8')
            # http://stackoverflow.com/questions/15793886/how-to-avoid-a-broken-pipe-error-when-printing-a-large-amount-of-formatted-data
            sys.stdout.flush()

    def __get_row(self, alloc, entity, row, only_these_fields, transforms=None):
        # Load up the items for one row for a pretty table or csv output.
        r = []
        for v in only_these_fields[::2]:
            value = ''
            success = False

            if v in row:
                if v == "tags":
                    try:
                        value = "[" + row[v].replace(", ", "][") + "]"
                        success = True
                    except:
                        value = row[v]
                        success = True
                else:
                    value = row[v]
                    success = True

            if transforms and v in transforms:
                value = transforms[v](value)
                success = True

            if v in alloc.user_transforms:
                value = alloc.user_transforms[v](value, row)
                success = True

            if v in alloc.field_names[entity]:
                other_v = alloc.field_names[entity][v]
                if other_v in alloc.user_transforms:
                    value = alloc.user_transforms[other_v](value, row)
                    success = True

            if not success:
                alloc.err('Bad field name: ' + str(v))

            if not value:
                value = ''

            r.append(value)
        return r
