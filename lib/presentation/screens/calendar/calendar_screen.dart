import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:intl/intl.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../domain/entities/case.dart';
import '../../../core/themes/app_colors.dart';
import '../cases/case_detail_screen.dart';
import '../cases/case_creation_flow_screen.dart';

class CalendarScreen extends ConsumerStatefulWidget {
  const CalendarScreen({super.key});

  @override
  ConsumerState<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends ConsumerState<CalendarScreen> {
  late final ValueNotifier<List<Case>> _selectedEvents;
  CalendarFormat _calendarFormat = CalendarFormat.month;
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;

  @override
  void initState() {
    super.initState();
    _selectedDay = _focusedDay;
    _selectedEvents = ValueNotifier(_getEventsForDay(_selectedDay!));
  }

  @override
  void dispose() {
    _selectedEvents.dispose();
    super.dispose();
  }

  List<Case> _getEventsForDay(DateTime day) {
    final user = ref.read(currentUserProvider);
    if (user == null) return [];

    final caseListState = ref.read(caseListProvider(user.email));
    return caseListState.cases.where((caseItem) {
      return isSameDay(caseItem.date, day);
    }).toList();
  }

  void _onDaySelected(DateTime selectedDay, DateTime focusedDay) {
    if (!isSameDay(_selectedDay, selectedDay)) {
      setState(() {
        _selectedDay = selectedDay;
        _focusedDay = focusedDay;
      });

      _selectedEvents.value = _getEventsForDay(selectedDay);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(currentUserProvider);

    if (user == null) {
      return const Scaffold(
        body: Center(
          child: Text('Please log in to view calendar'),
        ),
      );
    }

    final caseListState = ref.watch(caseListProvider(user.email));

    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Calendar',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: AppColors.jclOrange,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.jclWhite),
        actions: [
          IconButton(
            icon: const Icon(Icons.today, color: AppColors.jclWhite),
            onPressed: () {
              setState(() {
                _focusedDay = DateTime.now();
                _selectedDay = DateTime.now();
                _selectedEvents.value = _getEventsForDay(DateTime.now());
              });
            },
            tooltip: 'Today',
          ),
        ],
      ),
      body: caseListState.isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.jclOrange))
          : Column(
              children: [
                // Calendar Widget
                Container(
                  color: AppColors.jclWhite,
                  child: TableCalendar<Case>(
                    firstDay: DateTime.utc(2020, 1, 1),
                    lastDay: DateTime.now().add(const Duration(days: 365)),
                    focusedDay: _focusedDay,
                    selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
                    calendarFormat: _calendarFormat,
                    eventLoader: _getEventsForDay,
                    startingDayOfWeek: StartingDayOfWeek.sunday,
                    calendarStyle: CalendarStyle(
                      outsideDaysVisible: false,
                      // Selected day styling
                      selectedDecoration: const BoxDecoration(
                        color: AppColors.jclOrange,
                        shape: BoxShape.circle,
                      ),
                      selectedTextStyle: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                      // Today's styling
                      todayDecoration: BoxDecoration(
                        color: AppColors.jclOrange.withAlpha((255 * 0.3).round()),
                        shape: BoxShape.circle,
                      ),
                      todayTextStyle: const TextStyle(
                        color: AppColors.jclGray,
                        fontWeight: FontWeight.bold,
                      ),
                      // Event marker styling
                      markerDecoration: const BoxDecoration(
                        color: AppColors.jclOrange,
                        shape: BoxShape.circle,
                      ),
                      markerSize: 6,
                      // Default day styling
                      defaultTextStyle: const TextStyle(
                        color: AppColors.jclGray,
                      ),
                      weekendTextStyle: const TextStyle(
                        color: AppColors.jclGray,
                      ),
                    ),
                    headerStyle: HeaderStyle(
                      formatButtonVisible: true,
                      titleCentered: true,
                      formatButtonTextStyle: const TextStyle(
                        color: AppColors.jclOrange,
                        fontSize: 14,
                      ),
                      formatButtonDecoration: BoxDecoration(
                        border: Border.all(color: AppColors.jclOrange),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      leftChevronIcon: const Icon(
                        Icons.chevron_left,
                        color: AppColors.jclOrange,
                      ),
                      rightChevronIcon: const Icon(
                        Icons.chevron_right,
                        color: AppColors.jclOrange,
                      ),
                      titleTextStyle: const TextStyle(
                        color: AppColors.jclGray,
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    daysOfWeekStyle: const DaysOfWeekStyle(
                      weekdayStyle: TextStyle(
                        color: AppColors.jclGray,
                        fontWeight: FontWeight.bold,
                      ),
                      weekendStyle: TextStyle(
                        color: AppColors.jclGray,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    onDaySelected: _onDaySelected,
                    onFormatChanged: (format) {
                      if (_calendarFormat != format) {
                        setState(() {
                          _calendarFormat = format;
                        });
                      }
                    },
                    onPageChanged: (focusedDay) {
                      _focusedDay = focusedDay;
                    },
                  ),
                ),
                const Divider(height: 1, color: AppColors.jclWhite),

                // Cases List
                Expanded(
                  child: Container(
                    color: AppColors.jclGray,
                    child: ValueListenableBuilder<List<Case>>(
                      valueListenable: _selectedEvents,
                      builder: (context, value, _) {
                        if (value.isEmpty) {
                          return Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.event_busy,
                                  size: 64,
                                  color: AppColors.jclWhite.withAlpha((255 * 0.3).round()),
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  'No cases on ${DateFormat.yMMMd().format(_selectedDay!)}',
                                  style: const TextStyle(
                                    color: AppColors.jclWhite,
                                    fontSize: 16,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                const Text(
                                  'Tap + to add a case',
                                  style: TextStyle(
                                    color: AppColors.jclOrange,
                                    fontSize: 14,
                                  ),
                                ),
                              ],
                            ),
                          );
                        }

                        return ListView.separated(
                          padding: const EdgeInsets.all(0),
                          itemCount: value.length,
                          separatorBuilder: (context, index) => const Divider(
                            height: 1,
                            color: AppColors.jclWhite,
                            thickness: 0.5,
                          ),
                          itemBuilder: (context, index) {
                            final caseItem = value[index];
                            return ListTile(
                              tileColor: AppColors.jclGray,
                              leading: Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: AppColors.jclWhite,
                                  shape: BoxShape.circle,
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(6.0),
                                  child: caseItem.imageName != null
                                      ? Image.asset(
                                          'assets/images/${caseItem.imageName}',
                                          fit: BoxFit.contain,
                                          errorBuilder: (context, error, stackTrace) {
                                            return const Icon(
                                              Icons.medical_services,
                                              color: AppColors.jclOrange,
                                              size: 24,
                                            );
                                          },
                                        )
                                      : const Icon(
                                          Icons.medical_services,
                                          color: AppColors.jclOrange,
                                          size: 24,
                                        ),
                                ),
                              ),
                              title: Text(
                                caseItem.procedureSurgery,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: AppColors.jclWhite,
                                  fontSize: 15,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 4),
                                  Text(
                                    caseItem.anestheticPlan,
                                    style: TextStyle(
                                      color: AppColors.jclWhite.withAlpha((255 * 0.7).round()),
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Surgery: ${caseItem.surgeryClass}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: AppColors.jclWhite.withAlpha((255 * 0.5).round()),
                                    ),
                                  ),
                                ],
                              ),
                              trailing: const Icon(
                                Icons.chevron_right,
                                color: AppColors.jclOrange,
                              ),
                              onTap: () {
                                Navigator.of(context).push(
                                  MaterialPageRoute(
                                    builder: (_) => CaseDetailScreen(
                                      caseId: caseItem.objectId,
                                    ),
                                  ),
                                ).then((_) {
                                  ref
                                      .read(caseListProvider(user.email)
                                          .notifier)
                                      .loadCases();
                                  _selectedEvents.value =
                                      _getEventsForDay(_selectedDay!);
                                });
                              },
                            );
                          },
                        );
                      },
                    ),
                  ),
                ),
              ],
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => const CaseCreationFlowScreen(),
            ),
          ).then((_) {
            ref.read(caseListProvider(user.email).notifier).loadCases();
            _selectedEvents.value = _getEventsForDay(_selectedDay!);
          });
        },
        backgroundColor: AppColors.jclOrange,
        child: const Icon(Icons.add, color: AppColors.jclWhite),
      ),
    );
  }
}
