'use client';

import React from 'react';
import { Globe } from 'lucide-react';
import { cn } from '@/lib/utils';

interface TimezoneSelectorProps {
  selectedTimezone: string;
  onTimezoneChange: (timezone: string) => void;
  className?: string;
}

const TIMEZONES = [
  { value: 'Asia/Kolkata', label: 'India (IST)', offset: '+05:30' },
  { value: 'America/New_York', label: 'New York (EST/EDT)', offset: '-05:00/-04:00' },
  { value: 'Europe/London', label: 'London (GMT/BST)', offset: '+00:00/+01:00' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)', offset: '+09:00' },
  { value: 'Australia/Sydney', label: 'Sydney (AEDT/AEST)', offset: '+11:00/+10:00' },
  { value: 'America/Los_Angeles', label: 'Los Angeles (PST/PDT)', offset: '-08:00/-07:00' },
  { value: 'Europe/Paris', label: 'Paris (CET/CEST)', offset: '+01:00/+02:00' },
  { value: 'Asia/Dubai', label: 'Dubai (GST)', offset: '+04:00' },
  { value: 'Asia/Singapore', label: 'Singapore (SGT)', offset: '+08:00' },
  { value: 'UTC', label: 'UTC (Universal)', offset: '+00:00' },
];

export function TimezoneSelector({ selectedTimezone, onTimezoneChange, className }: TimezoneSelectorProps) {
  return (
    <div className={cn("flex items-center gap-2", className)}>
      <Globe className="h-4 w-4 text-gray-500" />
      <select
        value={selectedTimezone}
        onChange={(e) => onTimezoneChange(e.target.value)}
        className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 text-sm"
      >
        {TIMEZONES.map((tz) => (
          <option key={tz.value} value={tz.value}>
            {tz.label} ({tz.offset})
          </option>
        ))}
      </select>
    </div>
  );
}
