# Dashboard Testing Guide

## Quick Start

1. **Login as Admin**:
   ```
   Email: admin@unpatti.ac.id
   Password: password
   ```

2. **Navigate to Dashboard**:
   ```
   Sidebar → Monitoring Prestasi → Dashboard
   OR
   Direct URL: http://localhost/admin/achievements/dashboard
   ```

## Test Scenarios

### Scenario 1: View All Periods (Default)
**Steps**:
1. Access dashboard
2. Period filter should show "Semua Periode" by default (or active period)
3. Verify you see:
   - 4 statistics cards (Total, Pending, Approval Rate, Avg Time)
   - Period comparison bar chart (if "Semua Periode" selected)
   - Level distribution doughnut chart
   - Pending review list
   - Recent submissions list

**Expected Results**:
- Total achievements: 177
- Period comparison chart shows 3 bars for each period
- All statistics reflect data from all periods

### Scenario 2: Filter by Semester Ganjil 2024/2025
**Steps**:
1. Select "Semester Ganjil 2024/2025 (Aktif)" from period dropdown
2. Page should reload with filtered data

**Expected Results**:
- Purple info badge appears showing period details
- Total achievements: 49
- Monthly trend chart shows 5 months: Sep 2024 - Jan 2025
- Chart title: "Trend Submission Ganjil 2024/2025"
- Statistics cards update to show only this period's data
- Pending review list shows only achievements from this period

**Monthly Data**:
```
Sep 2024: 8 submitted, 4 approved
Oct 2024: 7 submitted, 1 approved
Nov 2024: 12 submitted, 8 approved
Dec 2024: 13 submitted, 7 approved
Jan 2025: 9 submitted, 6 approved
```

### Scenario 3: Filter by Semester Genap 2024/2025
**Steps**:
1. Select "Semester Genap 2024/2025" from period dropdown
2. Page should reload with filtered data

**Expected Results**:
- Total achievements: 62
- Monthly trend chart shows 6 months: Feb 2025 - Jul 2025
- Chart title: "Trend Submission Genap 2024/2025"

**Monthly Data**:
```
Feb 2025: 12 submitted, 5 approved
Mar 2025: 9 submitted, 6 approved
Apr 2025: 13 submitted, 6 approved
May 2025: 11 submitted, 7 approved
Jun 2025: 6 submitted, 4 approved
Jul 2025: 11 submitted, 5 approved
```

### Scenario 4: Filter by Semester Ganjil 2025/2026
**Steps**:
1. Select "Semester Ganjil 2025/2026" from period dropdown
2. Page should reload with filtered data

**Expected Results**:
- Total achievements: 39
- Monthly trend chart shows 5 months: Sep 2025 - Jan 2026
- Chart title: "Trend Submission Ganjil 2025/2026"
- Note: Some dates are in the future (this is test data)

**Monthly Data**:
```
Sep 2025: 9 submitted, 4 approved
Oct 2025: 8 submitted, 1 approved
Nov 2025: 9 submitted, 4 approved
Dec 2025: 7 submitted, 1 approved
Jan 2026: 6 submitted, 4 approved
```

### Scenario 5: Switch Back to All Periods
**Steps**:
1. Select "Semua Periode" from period dropdown
2. Page should reload

**Expected Results**:
- Period info badge disappears
- Monthly trend chart is replaced with period comparison chart
- Period comparison shows all 3 periods side by side
- Statistics show totals across all periods

## Visual Checks

### ✅ Statistics Cards
- [ ] Cards have gradient backgrounds (purple for total)
- [ ] Icons are visible and properly colored
- [ ] Numbers are large and readable
- [ ] Hover effect works (shadow increases)
- [ ] Dark mode colors work correctly

### ✅ Period Filter
- [ ] Dropdown is visible in top-right corner
- [ ] Shows all 3 periods + "Semua Periode" option
- [ ] Active period has "(Aktif)" label
- [ ] Selecting period triggers page reload
- [ ] Selected period is highlighted in dropdown

### ✅ Period Info Badge
- [ ] Only appears when specific period selected
- [ ] Purple background with border
- [ ] Shows period name, date range, and active status
- [ ] Calendar icon is visible
- [ ] Green "Periode Aktif" badge for active period

### ✅ Monthly Trend Chart
- [ ] Only shows when specific period selected
- [ ] X-axis shows correct months for selected period
- [ ] Y-axis starts at 0
- [ ] Two lines: purple (submitted) and green (approved)
- [ ] Lines have smooth curves (tension: 0.4)
- [ ] Points are visible on lines
- [ ] Hover shows tooltip with data
- [ ] Chart title includes semester and year
- [ ] Legend shows "Submitted" and "Approved"

### ✅ Period Comparison Chart
- [ ] Only shows when "Semua Periode" selected
- [ ] Bar chart with 4 datasets
- [ ] Colors: purple (total), green (approved), yellow (pending), red (rejected)
- [ ] X-axis shows period names
- [ ] Bars are grouped by period
- [ ] Legend shows all 4 categories
- [ ] Hover shows tooltip with data

### ✅ Level Distribution Chart
- [ ] Doughnut chart in center
- [ ] Shows 3 levels: Universitas, Nasional, Internasional
- [ ] Colors: purple, blue, green
- [ ] Legend at bottom
- [ ] Hover shows percentage
- [ ] Updates when period filter changes

### ✅ Pending Review List
- [ ] Shows up to 10 pending achievements
- [ ] Each item has student avatar (gradient circle with initial)
- [ ] Shows event name, student name, level, and time ago
- [ ] "Review" button on right side
- [ ] Hover effect on items (border color changes)
- [ ] Empty state shows when no pending items
- [ ] Filtered by selected period

### ✅ Recent Submissions List
- [ ] Shows up to 5 recent achievements
- [ ] Each item has achievement icon (gradient circle)
- [ ] Shows event name, student name, level, and category
- [ ] "Detail" button on right side
- [ ] Hover effect on items
- [ ] Empty state shows when no items
- [ ] Filtered by selected period

## Dark Mode Testing

1. Toggle dark mode (if available in your system)
2. Verify:
   - [ ] Background colors change appropriately
   - [ ] Text remains readable
   - [ ] Charts use dark-mode colors
   - [ ] Cards have dark backgrounds
   - [ ] Borders are visible but subtle
   - [ ] Gradients still look good

## Responsive Testing

### Desktop (1920x1080)
- [ ] All elements fit comfortably
- [ ] Charts are large and readable
- [ ] 4 statistics cards in one row
- [ ] 2 charts side by side (when applicable)
- [ ] 2 lists side by side

### Tablet (768x1024)
- [ ] Statistics cards stack to 2x2 grid
- [ ] Charts stack vertically
- [ ] Lists stack vertically
- [ ] Period filter remains accessible

### Mobile (375x667)
- [ ] Statistics cards stack vertically
- [ ] All charts stack vertically
- [ ] Period filter dropdown works
- [ ] Touch interactions work
- [ ] Scrolling is smooth

## Performance Checks

- [ ] Page loads in < 2 seconds
- [ ] No console errors
- [ ] Charts render smoothly
- [ ] Period filter change is fast
- [ ] No layout shifts during load

## Data Integrity Checks

1. **Verify Totals**:
   - Sum of all periods should equal "Semua Periode" total
   - Semester Ganjil 2024/2025: 49
   - Semester Genap 2024/2025: 62
   - Semester Ganjil 2025/2026: 39
   - Total: 150 (from LargeDataSeeder) + 27 (from PrestasiMahasiswaSeeder) = 177

2. **Verify Monthly Sums**:
   - Sum of monthly data should equal period total
   - Each month should have data (no empty months)

3. **Verify Status Distribution**:
   - Pending + Approved + Rejected + Revision = Total
   - Approval rate should be reasonable (40-60%)

## Common Issues & Solutions

### Issue: Charts not showing
**Solution**: Check browser console for JavaScript errors. Ensure Chart.js CDN is loaded.

### Issue: Wrong months displayed
**Solution**: Verify period dates in database. Check `getMonthlyTrend()` method logic.

### Issue: Period filter not working
**Solution**: Check that form submits correctly. Verify route accepts `period` parameter.

### Issue: Data doesn't match
**Solution**: Re-run seeder: `php artisan migrate:fresh --seed`

### Issue: Dark mode colors wrong
**Solution**: Check `isDark` variable in JavaScript. Verify Tailwind dark mode classes.

## Success Criteria

✅ All test scenarios pass
✅ All visual checks complete
✅ Dark mode works correctly
✅ Responsive design works on all screen sizes
✅ No console errors
✅ Data integrity verified
✅ Performance is acceptable

## Next Steps After Testing

If all tests pass:
1. ✅ Mark feature as complete
2. ✅ Document any edge cases found
3. ✅ Train users on new features
4. ✅ Monitor for issues in production

If issues found:
1. Document the issue with screenshots
2. Note steps to reproduce
3. Report to development team
4. Retest after fixes applied
