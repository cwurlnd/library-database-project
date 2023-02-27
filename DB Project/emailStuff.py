import smtplib
import ssl
from email.message import EmailMessage

import mysql.connector
import datetime
from datetime import date

cnx = mysql.connector.connect(user='cwurl', password='pwpwpwpw', host='localhost', database='cwurl')
cursor = cnx.cursor()

query = ("SELECT C.time, D.email FROM checkouts C, customers D WHERE C.cust_id = D.id")

cursor.execute(query)

for (time, email, ) in cursor:
  sendEmail = False
  returnDate = time + datetime.timedelta(days=14)
  today = date.today()
  tomorrow = today + datetime.timedelta(days=1)

  # Define email sender and receiver
  email_sender = 'cwurl@nd.edu'
  email_password = 'iuzddqdhzsileonn'
  email_receiver = email

  # Set the subject and body of the email
  subject = 'Overdue Book Update'

  if returnDate == today:
    body = """
    Hello! This is your local library with a quick notice. You currently have a book checked out.
    The return date for that book is today, so please stop by whenever to return the book.
    Otherwise, you will face a small charge for each day overdue. Thank you!

    - Your Local Library 
    """
    sendEmail = True
  elif returnDate == tomorrow:
    body = """
    Hello! This is your local library with a quick notice. You currently have a book checked out.
    The return date for that book is tomorrow, so please stop by soon to return the book.
    Otherwise, you will face a small charge for each day overdue. Thank you!

    - Your Local Library 
    """
    sendEmail = True
  elif returnDate < today:
    diff = today - returnDate
    charges = diff.days * .25
    body = """
    Hello! This is your local library with a quick notice. You currently have a book checked out.
    The return date for that book has passed, so please stop by soon to return the book.
    You currently have charges for that book being overdue, amounting to ${}.
    You will continue face a small charge for each day overdue. Thank you!

    - Your Local Library 
    """.format(charges)
    sendEmail = True

  if sendEmail:
    em = EmailMessage()
    em['From'] = email_sender
    em['To'] = email_receiver
    em['Subject'] = subject
    em.set_content(body)

    # Add SSL (layer of security)
    context = ssl.create_default_context()

    # Log in and send the email
    with smtplib.SMTP_SSL('smtp.gmail.com', 465, context=context) as smtp:
        smtp.login(email_sender, email_password)
        smtp.sendmail(email_sender, email_receiver, em.as_string())

cursor.close()
cnx.close()