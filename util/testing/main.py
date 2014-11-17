#!/usr/bin/python

# -*- coding: utf-8 -*-

# So we clear() input fields first to make sure there is nothing in them.

from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest
import time
import random


class Test(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Firefox()
        self.driver.implicitly_wait(30)
        self.base_url = "http://alloc-scratch.cyber.com.au/"
        self.verificationErrors = []
        self.accept_next_alert = True

    def test_(self):
        driver = self.driver
        word_file = "/usr/share/dict/words"
        words = open(word_file).read().splitlines()
        word = random.choice(words)
        # Login test:
        driver.get(self.base_url + "/login/login.php")
        driver.find_element_by_id("username").clear()
        driver.find_element_by_id("username").send_keys("cjb")
        driver.find_element_by_id("password").clear()
        driver.find_element_by_id("password").send_keys("password")
        driver.find_element_by_name("login").click()
        # Tasks tests:
        driver.find_element_by_link_text("Tasks").click()
        driver.find_element_by_link_text("New Task").click()
        driver.find_element_by_id("taskName").clear()
        driver.find_element_by_id("taskName").send_keys(word)
        Select(driver.find_element_by_id("projectID")).select_by_visible_text("Cyber PrisonPC")
        driver.find_element_by_id("taskDescription").clear()
        driver.find_element_by_id("taskDescription").send_keys(word)
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_css_selector("input.selectn-cb").click()
        driver.find_element_by_name("timeBest").click()
        driver.find_element_by_name("timeBest").clear()
        driver.find_element_by_name("timeBest").send_keys("1")
        driver.find_element_by_name("timeExpected").clear()
        driver.find_element_by_name("timeExpected").send_keys("2")
        driver.find_element_by_name("timeWorst").clear()
        driver.find_element_by_name("timeWorst").send_keys("3")
        driver.find_element_by_name("timeLimit").clear()
        driver.find_element_by_name("timeLimit").send_keys("2")
        Select(driver.find_element_by_name("managerID")).select_by_visible_text("Con Zymaris")
        Select(driver.find_element_by_name("personID")).select_by_visible_text("")
        driver.find_element_by_xpath("(//button[@name='save'])[2]").click()
        # make a comment
        driver.find_element_by_id("sbs_link_comments").click()
        driver.find_element_by_link_text("New Comment").click()
        driver.find_element_by_id("comment").clear()
        driver.find_element_by_id("comment").send_keys("Test")
        driver.find_element_by_name("comment_save").click()
        # reply to comment
        driver.find_element_by_link_text("reply").click()
        driver.find_element_by_id("comment").clear()
        driver.find_element_by_id("comment").send_keys("Test")
        driver.find_element_by_name("comment_save").click()
        # Home page tests:
        driver.get(self.base_url + "/home/home.php?showProject=on&showParentID=on&projectType=Current&search=&search=&search=&search=&taskTypeID%5B%5D=Task&taskTypeID%5B%5D=Parent&search=&personID%5B%5D=194&search=&taskDate=&dateOne=&dateTwo=&search=&limit=&applyFilter=1&sessID=")
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showParentID").click()
        driver.find_element_by_id("showProject").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showTags").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showTags").click()
        driver.find_element_by_id("showPriority").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showPercent").click()
        driver.find_element_by_id("showPriority").click()
        driver.find_element_by_id("showAssigned").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showAssigned").click()
        driver.find_element_by_id("showPercent").click()
        driver.find_element_by_id("showTimes").click()
        driver.find_element_by_id("showManager").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showCreator").click()
        driver.find_element_by_id("showDates").click()
        driver.find_element_by_id("showManager").click()
        driver.find_element_by_id("showTimes").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_id("showDates").click()
        driver.find_element_by_id("showCreator").click()
        driver.find_element_by_id("showProject").click()
        # The Assigned to menu:
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[6]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label > input.selectn-cb").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[6]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label.selectn-cb-selected > input.selectn-cb").click()
        # The Managed by menu:
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[4]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[4]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_name("applyFilter").click()
        # The Created by menu:
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[2]/td[3]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[2]/td[3]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_name("applyFilter").click()
        # Projects menu:
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_name("all").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_name("none").click()
        driver.find_element_by_name("applyFilter").click()
        # The Task Status menu:
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[4]/td/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[4]/td/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_name("applyFilter").click()
        # The Tags menu:
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[7]/td").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[6]/td/span").click()
        driver.find_element_by_xpath("//input[@value='Parent']").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[8]/td").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_id("config_top_ten_tasks").click()
        driver.find_element_by_xpath("//div[@id='main2']/div/table/tbody/tr/th/span/div/form/table/tbody/tr[10]/td/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label > input.selectn-cb").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label.selectn-cb-selected").click()
        driver.find_element_by_name("applyFilter").click()
        # Test the calendar
        driver.find_element_by_id("config_task_calendar_home_item").click()
        Select(driver.find_element_by_name("weeks")).select_by_visible_text("3")
        Select(driver.find_element_by_name("weeksBack")).select_by_visible_text("2")
        driver.find_element_by_name("customize_save").click()
        driver.find_element_by_id("config_task_calendar_home_item").click()
        Select(driver.find_element_by_name("weeks")).select_by_visible_text("4")
        Select(driver.find_element_by_name("weeksBack")).select_by_visible_text("1")
        driver.find_element_by_name("customize_save").click()
        # Test a link in the calendar
        driver.find_element_by_link_text("Test 00001 (Limit 50.0hrs)").click()
        driver.find_element_by_link_text("Home").click()
        # Time sheets
        driver.find_element_by_xpath("(//a[contains(text(),'Cy Support')])[2]").click()
        driver.find_element_by_link_text("Home").click()
        # Projects
        driver.find_element_by_id("config_project_list").click()
        Select(driver.find_element_by_name("projectListNum")).select_by_visible_text("All")
        driver.find_element_by_name("customize_save").click()
        driver.find_element_by_id("config_project_list").click()
        Select(driver.find_element_by_name("projectListNum")).select_by_visible_text("5")
        driver.find_element_by_name("customize_save").click()
        # Time sheet stats
        driver.find_element_by_css_selector("canvas.jqplot-event-canvas").click()
        # Clients
        driver.find_element_by_link_text("Clients").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_name("none").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("img").click()
        driver.find_element_by_css_selector("input.selectn-cb").click()
        driver.find_element_by_link_text("P").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[5]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label > input.selectn-cb").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label.selectn-cb-selected > span").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_link_text("P").click()
        # Add a client
        driver.find_element_by_link_text("New Client").click()
        driver.find_element_by_id("clientName").clear()
        driver.find_element_by_id("clientName").send_keys(word)
        driver.find_element_by_name("clientStreetAddressOne").click()
        driver.find_element_by_name("clientStreetAddressOne").clear()
        driver.find_element_by_name("clientStreetAddressOne").send_keys(word)
        driver.find_element_by_name("clientSuburbOne").clear()
        driver.find_element_by_name("clientSuburbOne").send_keys(word)
        driver.find_element_by_name("clientStateOne").clear()
        driver.find_element_by_name("clientStateOne").send_keys(word)
        driver.find_element_by_name("clientPostcodeOne").clear()
        driver.find_element_by_name("clientPostcodeOne").send_keys("1029")
        driver.find_element_by_name("clientCountryOne").clear()
        driver.find_element_by_name("clientCountryOne").send_keys(word)
        driver.find_element_by_name("clientPhoneOne").clear()
        driver.find_element_by_name("clientPhoneOne").send_keys("01928374")
        driver.find_element_by_name("clientURL").clear()
        driver.find_element_by_name("clientURL").send_keys(word + ".com")
        driver.find_element_by_name("save").click()
        # Project filter test
        driver.find_element_by_link_text("Projects").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_css_selector("input.selectn-cb").click()
        driver.find_element_by_name("projectName").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[3]/span/img").click()
        driver.find_element_by_xpath("//input[@value='181']").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label > input.selectn-cb").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > label.selectn-cb-selected > span").click()
        driver.find_element_by_name("applyFilter").click()
        # Make a new project.
        driver.find_element_by_link_text("Projects").click()
        driver.find_element_by_link_text("New Project").click()
        driver.find_element_by_id("projectName").clear()
        driver.find_element_by_id("projectName").send_keys(word)
        driver.find_element_by_id("projectComments").clear()
        driver.find_element_by_id("projectComments").send_keys(word)
        Select(driver.find_element_by_css_selector("#clientDropdown > #clientID")).select_by_visible_text("aaaaaa")
        driver.find_element_by_name("projectShortName").clear()
        driver.find_element_by_name("projectShortName").send_keys(word)
        driver.find_element_by_name("projectBudget").clear()
        driver.find_element_by_name("projectBudget").send_keys("1000")
        driver.find_element_by_css_selector("div.edit > button[name=\"save\"]").click()
        # Time tests
        driver.find_element_by_link_text("Time").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("#projectDropdown > span.selectn-label").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("#projectDropdown > span.selectn-label").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("#projectDropdown > span.selectn-label").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_xpath("//input[@value='33']").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("img").click()
        driver.find_element_by_css_selector("input.selectn-cb").click()
        driver.find_element_by_css_selector("label > span").click()
        driver.find_element_by_css_selector("input.selectn-cb").click()
        driver.find_element_by_css_selector("label.selectn-cb-selected > span").click()
        driver.find_element_by_name("applyFilter").click()
        # Create a time sheet.
        driver.find_element_by_link_text("New Time Sheet").click()
        Select(driver.find_element_by_id("projectID")).select_by_visible_text(".aaaaaaa [Project]")
        driver.find_element_by_name("save").click()
        driver.find_element_by_id("calendar_1").clear()
        driver.find_element_by_id("calendar_1").send_keys("2014-11-11")
        driver.find_element_by_name("timeSheetItem_timeSheetItemDuration").clear()
        driver.find_element_by_name("timeSheetItem_timeSheetItemDuration").send_keys("1")
        driver.find_element_by_name("timeSheetItem_rate").clear()
        driver.find_element_by_name("timeSheetItem_rate").send_keys("25")
        driver.find_element_by_id("timeSheetItem_comment").clear()
        driver.find_element_by_id("timeSheetItem_comment").send_keys("afsd")
        driver.find_element_by_name("timeSheetItem_save").click()
        driver.find_element_by_name("save").click()
        # Sales tests.
        driver.find_element_by_link_text("Sales").click()
        driver.find_element_by_link_text("85").click()
        driver.find_element_by_link_text("Sales").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_name("all").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[3]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"all\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Show Filter").click()
        driver.find_element_by_css_selector("span.selectn-label").click()
        driver.find_element_by_name("none").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[2]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_xpath("//div[@id='main2']/table/tbody/tr[2]/td/form/table/tbody/tr[2]/td[3]/span").click()
        driver.find_element_by_css_selector("span.selectn-dropdown.selectn-active > div.selectn-buttons > button[name=\"none\"]").click()
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Products").click()
        driver.find_element_by_link_text("ATENKVM8").click()
        driver.find_element_by_link_text("Sales").click()
        # People tests
        driver.find_element_by_link_text("People").click()
        driver.find_element_by_css_selector("tr.even > td > a").click()
        driver.find_element_by_link_text("People").click()
        driver.find_element_by_link_text("Show Filter").click()
        Select(driver.find_element_by_name("skill_class")).select_by_visible_text("Unix")
        Select(driver.find_element_by_name("skill")).select_by_visible_text("Unix - BSD")
        driver.find_element_by_name("applyFilter").click()
        Select(driver.find_element_by_name("skill_class")).select_by_visible_text("Any Class")
        Select(driver.find_element_by_name("skill")).select_by_visible_text("Any Skill")
        driver.find_element_by_name("applyFilter").click()
        driver.find_element_by_link_text("Person Graphs").click()
        driver.find_element_by_link_text("People").click()
        driver.find_element_by_link_text("Skill Matrix").click()
        # Wiki tests
        driver.find_element_by_link_text("Wiki").click()
        driver.find_element_by_id("newFile").click()
        driver.find_element_by_id("editName").click()
        driver.find_element_by_id("editName").clear()
        driver.find_element_by_id("editName").send_keys(word)
        driver.find_element_by_id("wikitext").click()
        driver.find_element_by_id("wikitext").clear()
        driver.find_element_by_id("wikitext").send_keys(word)
        driver.find_element_by_id("save").click()
        driver.find_element_by_id("commit_msg").clear()
        driver.find_element_by_id("commit_msg").send_keys(word)
        driver.find_element_by_id("save").click()
        driver.find_element_by_id("newDirectory").click()
        driver.find_element_by_id("dirName").clear()
        driver.find_element_by_id("dirName").send_keys(word)
        driver.find_element_by_id("save").click()
        # test the search box
        driver.find_element_by_id("menu_form_needle").clear()
        driver.find_element_by_id("menu_form_needle").send_keys("test00019")
        driver.find_element_by_id("menu_form_needle").send_keys(Keys.RETURN)
        # Logout test:
        driver.find_element_by_link_text("Logout").click()
        time.sleep(2)

    def is_element_present(self, how, what):
        try:
            self.driver.find_element(by=how, value=what)
        except NoSuchElementException, e:
            return False
        return True

    def is_alert_present(self):
        try:
            self.driver.switch_to_alert()
        except NoAlertPresentException, e:
            return False
        return True

    def close_alert_and_get_its_text(self):
        try:
            alert = self.driver.switch_to_alert()
            alert_text = alert.text
            if self.accept_next_alert:
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally:
            self.accept_next_alert = True

    def tearDown(self):
        self.driver.quit()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
