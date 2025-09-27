import { format } from 'date-fns';

/**
 * Convert a UTC date to a specific timezone and format it
 */
export function formatDateInTimezone(
  date: string | Date, 
  timezone: string, 
  formatString: string = 'MMM dd, yyyy • h:mm a'
): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  
  // Use toLocaleString with timezone
  try {
    const options: Intl.DateTimeFormatOptions = {
      timeZone: timezone,
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    };
    
    const formatted = dateObj.toLocaleString('en-US', options);
    return formatted.replace(/,/g, ' •');
  } catch (error) {
    // Fallback to regular formatting if timezone is invalid
    return format(dateObj, formatString);
  }
}

/**
 * Get timezone abbreviation
 */
export function getTimezoneAbbreviation(timezone: string): string {
  const abbrevMap: Record<string, string> = {
    'Asia/Kolkata': 'IST',
    'America/New_York': 'EST/EDT',
    'Europe/London': 'GMT/BST',
    'Asia/Tokyo': 'JST',
    'Australia/Sydney': 'AEDT/AEST',
    'America/Los_Angeles': 'PST/PDT',
    'Europe/Paris': 'CET/CEST',
    'Asia/Dubai': 'GST',
    'Asia/Singapore': 'SGT',
    'UTC': 'UTC',
  };
  
  return abbrevMap[timezone] || timezone;
}

/**
 * Format event time range in a specific timezone
 */
export function formatEventTimeRange(
  startTime: string | Date,
  endTime: string | Date,
  timezone: string
): string {
  const start = typeof startTime === 'string' ? new Date(startTime) : startTime;
  const end = typeof endTime === 'string' ? new Date(endTime) : endTime;
  
  try {
    const startOptions: Intl.DateTimeFormatOptions = {
      timeZone: timezone,
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    };
    
    const endTimeOptions: Intl.DateTimeFormatOptions = {
      timeZone: timezone,
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    };
    
    const startFormatted = start.toLocaleString('en-US', startOptions);
    const endTimeFormatted = end.toLocaleString('en-US', endTimeOptions);
    
    // Check if same day
    const startDay = start.toLocaleDateString('en-CA', { timeZone: timezone });
    const endDay = end.toLocaleDateString('en-CA', { timeZone: timezone });
    
    if (startDay === endDay) {
      return `${startFormatted.replace(/,/g, ' •')} - ${endTimeFormatted}`;
    } else {
      const endFullFormatted = end.toLocaleString('en-US', startOptions);
      return `${startFormatted.replace(/,/g, ' •')} - ${endFullFormatted.replace(/,/g, ' •')}`;
    }
  } catch (error) {
    // Fallback formatting
    return `${format(start, 'MMM dd, yyyy • h:mm a')} - ${format(end, 'h:mm a')}`;
  }
}

/**
 * Calculate event duration in hours and minutes
 */
export function calculateEventDuration(
  startTime: string | Date,
  endTime: string | Date
): string {
  const start = typeof startTime === 'string' ? new Date(startTime) : startTime;
  const end = typeof endTime === 'string' ? new Date(endTime) : endTime;
  
  const durationMs = end.getTime() - start.getTime();
  const hours = Math.floor(durationMs / (1000 * 60 * 60));
  const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
  
  if (hours === 0) {
    return `${minutes} minutes`;
  } else if (minutes === 0) {
    return `${hours} hour${hours !== 1 ? 's' : ''}`;
  } else {
    return `${hours}h ${minutes}m`;
  }
}

/**
 * Check if event is happening now in a specific timezone
 */
export function isEventHappeningNow(
  startTime: string | Date,
  endTime: string | Date,
  timezone: string
): boolean {
  const now = new Date();
  const start = typeof startTime === 'string' ? new Date(startTime) : startTime;
  const end = typeof endTime === 'string' ? new Date(endTime) : endTime;
  
  return now >= start && now <= end;
}

/**
 * Get user's detected timezone
 */
export function getUserTimezone(): string {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
  } catch {
    return 'Asia/Kolkata'; // Fallback to IST
  }
}
