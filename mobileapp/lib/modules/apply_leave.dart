import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:setu_printers/config/app_config.dart';
import 'package:setu_printers/config/theme/app_colors.dart';
import 'package:setu_printers/config/theme/app_text_style.dart';
import 'package:setu_printers/manager/global_singleton.dart';
import 'package:setu_printers/network/network_dio.dart';

class ApplyLeave extends StatefulWidget {
    const ApplyLeave({super.key});

    @override
    State<ApplyLeave> createState() => _ApplyLeaveState();
}

class _ApplyLeaveState extends State<ApplyLeave> {
    final _formKey = GlobalKey<FormState>();

    final Set<DateTime> _selectedDates = {};
    final TextEditingController _reasonController = TextEditingController();

    DateTime _focusedDay = DateTime.now();

    @override
    void dispose() {
        _reasonController.dispose();
        super.dispose();
    }

    DateTime _normalizeDate(DateTime date) {
        return DateTime(date.year, date.month, date.day);
    }

    void _onDaySelected(DateTime selectedDay, DateTime focusedDay) {
        setState(() {
            _focusedDay = focusedDay;
            final normalized = _normalizeDate(selectedDay);
            if (_selectedDates.contains(normalized)) {
                _selectedDates.remove(normalized);
            } else {
                _selectedDates.add(normalized);
            }
        });
    }

    Future<void> _submitLeave() async {
        if (!_formKey.currentState!.validate()) return;

        if (_selectedDates.isEmpty) {
            ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text("Please select at least one date")),
            );
            return;
        }

        final staffId = GlobalSingleton.selectedUser?.userId;
        if (staffId == null) {
            ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text("No user selected")),
            );
            return;
        }

        final sortedDates = _selectedDates.toList()..sort();
        final dateStrings = sortedDates
            .map((d) =>
                '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}')
            .toList();

        final data = {
            'staff_id': int.parse(staffId),
            'dates': dateStrings,
            'reason': _reasonController.text.trim(),
        };

        log('Submitting leave: $data');

        final response = await NetworkDio.postData(
            url: ApiPath.baseUrl + ApiPath.applyLeave,
            data: data,
            context: context,
        );

        if (!mounted) return;

        if (response != null && response['status'] == true) {
            showDialog(
                context: context,
                barrierDismissible: false,
                builder: (ctx) {
                    return Dialog(
                        insetPadding: const EdgeInsets.symmetric(horizontal: 15),
                        shape: const RoundedRectangleBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10.0)),
                        ),
                        child: Container(
                            decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                color: Colors.white,
                            ),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                            child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                    Text(
                                        "Success",
                                        style: AppTextStyle.semiBold20.copyWith(
                                            decoration: TextDecoration.underline,
                                        ),
                                    ),
                                    const SizedBox(height: 10),
                                    const SizedBox(height: 10),
                                    Text(response['message'] ?? 'Leave applied successfully'),
                                    const SizedBox(height: 25),
                                    Align(
                                        alignment: Alignment.center,
                                        child: InkWell(
                                            onTap: () {
                                                Navigator.pop(ctx);
                                                Get.back();
                                            },
                                            child: Container(
                                                padding: const EdgeInsets.symmetric(
                                                    vertical: 8, horizontal: 40),
                                                decoration: BoxDecoration(
                                                    color: AppColors.appColors,
                                                    borderRadius: BorderRadius.circular(10.0),
                                                ),
                                                child: Text(
                                                    'OK',
                                                    textScaler: TextScaler.noScaling,
                                                    style: const TextStyle(
                                                        color: Colors.white,
                                                        fontSize: 20,
                                                        fontWeight: FontWeight.w600),
                                                ),
                                            ),
                                        ),
                                    ),
                                ],
                            ),
                        ),
                    );
                },
            );
        } else {
            final errorMsg = response?['message'] ?? 'Failed to apply for leave';
            ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(errorMsg)),
            );
        }
    }

    @override
    Widget build(BuildContext context) {
        return Scaffold(
            appBar: AppBar(
                title: Text("Apply for Leave", style: AppTextStyle.semiBold20),
            ),
            body: SafeArea(
                child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Form(
                        key: _formKey,
                        child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                                Text("Select Dates", style: AppTextStyle.semiBold16),
                                const SizedBox(height: 8),
                                Container(
                                    decoration: BoxDecoration(
                                        border: Border.all(color: AppColors.appColors, width: 1.0),
                                        borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: TableCalendar(
                                        firstDay: DateTime.now(),
                                        lastDay: DateTime.now().add(const Duration(days: 365)),
                                        focusedDay: _focusedDay,
                                        calendarFormat: CalendarFormat.month,
                                        selectedDayPredicate: (day) {
                                            return _selectedDates.contains(_normalizeDate(day));
                                        },
                                        onDaySelected: _onDaySelected,
                                        onPageChanged: (focusedDay) {
                                            _focusedDay = focusedDay;
                                        },
                                        calendarStyle: CalendarStyle(
                                            selectedDecoration: const BoxDecoration(
                                                color: AppColors.appColors,
                                                shape: BoxShape.circle,
                                            ),
                                            todayDecoration: BoxDecoration(
                                                color: AppColors.appColors.withValues(alpha: 0.3),
                                                shape: BoxShape.circle,
                                            ),
                                        ),
                                        headerStyle: const HeaderStyle(
                                            formatButtonVisible: false,
                                            titleCentered: true,
                                        ),
                                    ),
                                ),

                                const SizedBox(height: 8),

                                if (_selectedDates.isNotEmpty)
                                    Wrap(
                                        spacing: 6,
                                        runSpacing: 6,
                                        children: (_selectedDates.toList()..sort()).map((date) {
                                            return Chip(
                                                label: Text(
                                                    '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}',
                                                    style: AppTextStyle.regular12
                                                        .copyWith(color: AppColors.whiteColor),
                                                ),
                                                backgroundColor: AppColors.appColors,
                                                deleteIcon: const Icon(Icons.close,
                                                    size: 16, color: AppColors.whiteColor),
                                                onDeleted: () {
                                                    setState(() {
                                                        _selectedDates.remove(date);
                                                    });
                                                },
                                            );
                                        }).toList(),
                                    ),

                                const SizedBox(height: 20),

                                Text("Reason", style: AppTextStyle.semiBold16),
                                const SizedBox(height: 8),
                                TextFormField(
                                    controller: _reasonController,
                                    maxLines: 4,
                                    decoration: const InputDecoration(
                                        hintText: 'Enter reason for leave',
                                        contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                        border: OutlineInputBorder(
                                            borderSide: BorderSide(color: AppColors.appColors, width: 1.0),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                            borderSide: BorderSide(color: AppColors.appColors, width: 1.0),
                                        ),
                                    ),
                                    validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                            return 'Please enter a reason';
                                        }
                                        return null;
                                    },
                                ),

                                const SizedBox(height: 30),

                                Center(
                                    child: InkWell(
                                        onTap: _submitLeave,
                                        child: Container(
                                            padding: const EdgeInsets.symmetric(
                                                horizontal: 100, vertical: 12),
                                            decoration: BoxDecoration(
                                                color: AppColors.blueCOlor,
                                                borderRadius: BorderRadius.circular(30),
                                            ),
                                            child: Text(
                                                "Submit",
                                                style: AppTextStyle.semiBold20
                                                    .copyWith(color: AppColors.whiteColor),
                                            ),
                                        ),
                                    ),
                                ),

                                const SizedBox(height: 20),
                            ],
                        ),
                    ),
                ),
            ),
        );
    }
}
