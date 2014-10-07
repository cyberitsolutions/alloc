# -*- coding: utf-8 -*-
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re

class Test(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Firefox()
        self.driver.implicitly_wait(30)
        self.base_url = "http://alloc-scratch.cyber.com.au/"
        self.verificationErrors = []
        self.accept_next_alert = True
    
    def test_(self):
        driver = self.driver
        driver.get(self.base_url + "/login/login.php")
        driver.find_element_by_id("username").clear()
        driver.find_element_by_id("username").send_keys("cjb")
        driver.find_element_by_id("password").clear()
        driver.find_element_by_id("password").send_keys("password")
        driver.find_element_by_name("login").click()
        driver.find_element_by_link_text("Tasks").click()
        driver.find_element_by_link_text("New Task").click()
        driver.find_element_by_id("taskName").clear()
        driver.find_element_by_id("taskName").send_keys("test00009")
        Select(driver.find_element_by_id("projectID")).select_by_visible_text("Cyber PrisonPC")
        driver.find_element_by_id("taskDescription").clear()
        driver.find_element_by_id("taskDescription").send_keys("test")
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
        driver.find_element_by_css_selector("save_button default").click()
    
    def is_element_present(self, how, what):
        try: self.driver.find_element(by=how, value=what)
        except NoSuchElementException, e: return False
        return True
    
    def is_alert_present(self):
        try: self.driver.switch_to_alert()
        except NoAlertPresentException, e: return False
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
        finally: self.accept_next_alert = True
    
    def tearDown(self):
        self.driver.quit()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
